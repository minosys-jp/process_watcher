<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Hostname;
use App\Models\ProgramModule;
use App\Models\FingerPrint;
use App\Models\Graph;
use App\Models\Configure;
use App\Models\DiscordNotify;
use GuzzleHttp\Client;
use Carbon\Carbon;
class ApiController extends Controller
{
    private $discord_queue = [];

    //
    public function post(Request $request) {
    Log::debug("enter post");
        // パラメータ取り出し
        $tenant_code = $request->tenant;
        $domain_code = $request->domain;
        $host_code = $request->hostname;
        $fingers = $request->fingers;
        $graphs = $request->graphs;
        $type_id = DiscordNotify::TYPE_UPDATE;
        $xtable1 = [];
        $xtable2 = [];
	Log::debug("tenant:" . $tenant_code . ",domain:" . $domain_code . ",host:" . $host_code);

        // テナント、ドメインの登録を確認
        $tenant = Tenant::where('code', $tenant_code)->first();
        if ($tenant === null) {
            Log::debug("tenant not found");
            abort(404);
        }
        $rVal = [];
        try {
            DB::beginTransaction();
            $domain = Domain::select('domains.*')->where('domains.code', $domain_code)
                ->join('tenants', 'tenants.id', 'tenant_id')
                ->where('tenants.code', $tenant_code)
                ->first();
            if ($domain === null) {
                Log::debug("domain not found");
                abort(404);
            }
    
            // ホスト名を確認/未登録なら登録する
            $hostname = Hostname::select('hostnames.*')
                ->where('hostnames.code', $host_code)
                ->join('domains', 'hostnames.domain_id', 'domains.id')
                ->join('tenants', 'tenants.id', 'domains.tenant_id')
                ->where('domains.code', $domain_code)
                ->where('tenants.code', $tenant_code)
                ->first();
            if (!$hostname) {
                $hostname = new Hostname;
                $hostname->domain_id = $domain->id;
                $hostname->code = $host_code;
                $hostname->name = $host_code;
                $hostname->save();
		Log::debug("new host");
            }
    
            // フィンガープリントの更新
            if ($fingers && is_array($fingers)) {
Log::debug("fingers:" . count($fingers));
                foreach ($fingers as $finger) {
                    if (!array_key_exists('dbid', $finger) || !array_key_exists('name', $finger) || !array_key_exists('finger', $finger) || !array_key_exists('status', $finger)) {
                        continue;
                    }

                    $type_id = DiscordNotify::TYPE_UPDATE;
                    $fpid = $this->updateFingerPrint($finger, $hostname);
                    if ($fpid !== FALSE) {
                        $xtable1[$fpid] = $finger['dbid'];
                        $xtable2[$finger['dbid']] = $fpid;
                    }
                    if ($fpid !== FALSE && $finger['status'] !== 2) {
                        // discord notifier
                        $db = new DiscordNotify;
                        $db->tenant_id = $tenant->id;
                        $db->domain_id = $domain->id;
                        $db->hostname_id = $hostname->id;
                        $db->type_id = $type_id;
                        $db->finger_print_id = $fpid;
                        $db->save();
                    }
                }
            }

            // 実行ファイルグラフの更新
            $cache = [];
            if ($graphs && is_array($graphs)) {
Log::debug("graphs:" . count($graphs));
                foreach ($graphs as $graph) {
                    $exe = $graph['exe'];
                    if (!array_key_exists('dlls', $graph)) {
                        continue;
                    }
                    $dlls = $graph['dlls'];
                    $module_exe = $this->loadModule($cache, $exe, $hostname->id, $xtable2);
                    if (!$module_exe) {
                        Log::error("missing " . $exe);
                        continue;
                    }
                    foreach ($dlls as $dll) {
                        $module_dll = $this->loadModule($cache, $dll, $hostname->id, $xtable2);
                        if (!$module_dll) {
                            Log::error("missing " . $module_dll);
                            continue;
                        }
                        $ograph = new Graph;
                        $ograph->parent_id = $module_exe->id;
                        $ograph->parent_version = $module_exe->version;
                        $ograph->child_id = $module_dll->id;
                        $ograph->child_version = $module_dll->version;
                        $ograph->save();

                        // discord notifies
                        $db = new DiscordNotify;
                        $db->tenant_id = $tenant->id;
                        $db->domain_id = $domain->id;
                        $db->hostname_id = $hostname->id;
                        $db->type_id = DiscordNotify::TYPE_UPDATE;
                        $db->graph_id = $ograph->id;
                        $db->save();
                    }
                }
            }

            // black list の抽出
            $blist = ProgramModule::where('status', ProgramModule::FLG_BLACK)
                ->where('hostname_id', $hostname->id)
                ->where('notified', 0)
                ->pluck('id')
                ->toArray();
            $blist = array_map(function($e) use ($xtable1) {
                if (array_key_exists($e, $xtable1)) {
                    return $xtable1[$e];
                }
                return FALSE;
            }, $blist);
            $blist = array_filter(function($e) { return $e !== FALSE }, $blist);
            $rVal['black'] =$blist;

            // white list の抽出
            $wlist0 = ProgramModule::where('status', ProgramModule::FLG_WHITE)
                ->where('hostname_id', $hostname->id)
                ->where('notified', 0)
                ->pluck('id')
                ->toArray();
            $wlist = array_map(function($e) use ($xtable1) {
                if (array_key_exists($e, $xtable1)) {
                    return $xtable1[$e];
                }
                return FALSE;
            }, $wlist);
            $wlist = array_filter(function($e) { return $e !== FALSE }, $wlist);
            $rVal['white'] =$wlist;

            // black process の強制終了
            $nKillBlackProc = Configure::select('cnum')
                ->where('domain_id', $domain->id)
                ->where('tenant_id', $tenant->id)
                ->where('ckey', 'kill_black_processes')
                ->first();
            if ($nKillBlackProc === null) {
                // backtrack
                $nKillBlackProc = Configure::select('cnum')
                    ->where('tenant_id', $tenant->id)
                    ->where('ckey', 'kill_black_processes')
                    ->first();
            }
            if ($nKillBlackProc !== null) {
                $rVal['kill_black_processes'] = $nKillBlackProc->cnum;
            }
            if (count($blist) > 0) {
                $blist = array_map(function($e) use ($xtable2) { return $xtable2[$e]; }, $blist);
                ProgramModule::whereIn('id', $blist)->update(['notified' => 1]);
            }
            if (count($wlist) > 0) {
                $wlist = array_map(function($e) use ($xtable2) { return $xtable2[$e]; }, $wlist);
                ProgramModule::whereIn('id', $wlist)->update(['notified' => 1]);
            }

            DB::commit();
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
            return response()->json([ false, $e->getMessage() ]);
        }

        return response()->json([ true, $rVal ]);
    }

    private function updateFingerPrint($finger, $hostname) {
        $fprints = null;
        $proc = ProgramModule::where('name', $finger['name'])
            ->where('hostname_id', $hostname->id)
            ->first();
        if ($proc) {
            $fprints = FingerPrint::where('program_module_id', $proc->id)
                ->where('version', $proc->version)
                ->first();
        }
        if (!$fprints || $fprints->finger_print !== $finger['finger']) {
		if (!$finger['finger']) {
            Log::error("finger missing:" . $finger['name']);
            return FALSE;
        }
        if ($proc) {
            $proc->version += 1;
        } else {
            $proc = new ProgramModule;
            $proc->name = $finger['name'];
            $proc->hostname_id = $hostname->id;
            $proc->version = 1;
            $type_id = DiscordNotify::TYPE_NEW;
        }
        $proc->status = $finger['status'];
        $proc->save();

        $fprints = new FingerPrint;
        $fprints->program_module_id = $proc->id;
        $fprints->version = $proc->version;
        $fprints->finger_print = $finger['finger'];
        $fprints->save();
        return $fprints->id;
    }
    return FALSE;
}

    private function loadModule(&$cache, int $modid, int $hid, $xtable2) {
        if (isset($cache[$hid][$modid])) {
            return $cache[$hid][$modid];
        }
        if (!array_key_exists($hid, $cache)) {
            $cache[$hid] = [];
        }
        $pmod = ProgramModule::where('id', $xtable2[$modid])
            ->first();
        $cache[$hid][$modname] = $pmod;
        return $pmod;
    }
}
