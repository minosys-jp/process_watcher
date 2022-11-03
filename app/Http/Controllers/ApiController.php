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

        // テナント、ドメインの登録を確認
        $tenant = Tenant::where('code', $tenant_code)->first();
        if ($tenant === null) {
            abort(404);
        }
        try {
            DB::beginTransaction();
            $domain = Domain::select('domains.*')->where('domains.code', $domain_code)
                ->join('tenants', 'tenants.id', 'tenant_id')
                ->where('tenants.code', $tenant_code)
                ->first();
            if ($domain === null) {
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
            }
    
            // フィンガープリントの更新
            if ($fingers && is_array($fingers)) {
                foreach ($fingers as $finger) {
                    if (!array_key_exists('name', $finger) || !array_key_exists('finger', $finger) || array_key_exists('flg_white', $finger)) {
                        continue;
                    }

                    $type_id = DiscordNotify::TYPE_UPDATE;
                    if ($this->updateFingerPrint($finger, $hostname)) {
                        // discord notifier
                        $dn = new DiscordNotify;
                        $dn->tenant_id = $tenant->id;
                        $dn->domain_id = $domain->id;
                        $db->hostname_id = $hostname->id;
                        $db->type_id = $type_id;
                        $db->finger_print_id = $fprinst->id;
                        $db->save();
                    }
                }
            }

            // 実行ファイルグラフの更新
            $cache = [];
            if ($graphs && is_array($graphs)) {
                foreach ($graphs as $graph) {
                    $exe = $graph['exe'];
                    $dlls = $graph['dlls'];
                    $module_exe = $this->loadModule($cache, $exe, $hostname->id);
                    foreach ($dlls as $dll) {
                        $module_dll = $this->loadModule($cache, $dll, $hostname->id);
                        $graph = new Graph;
                        $graph->parent_id = $module_exe->id;
                        $graph->parent_version = $module_exe->version;
                        $graph->child_id = $module_dll->id;
                        $graph->child_version = $module_dll->version;
                        $graph->save();

                        // discord notifies
                        $dn = new DiscordNotify;
                        $dn->tenant_id = $tenant->id;
                        $db->domain_id = $domain->id;
                        $db->hostname_id = $hostname->id;
                        $db->type_id = DiscordNotify::TYPE_UPDATE;
                        $db->graph_id = $graph->id;
                        $db->save();
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([ false, $e->getMessage() ]);
        }

        return response()->json([ true, null ]);
    }

    private function updateFingerPrint($proc, $finger, $hostname) {
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
            if ($proc) {
                $proc->version += 1;
            } else {
                $proc = new ProgramModule;
                $proc->name = $finger['name'];
                $proc->hostname_id = $hostname->id;
                $proc->version = 1;
                $type_id = DiscordNotify::TYPE_NEW;
            }
            $proc->flg_white = $finger['flg_white'];
            $proc->save();

            $fprints = new FingerPrint;
            $fprints->program_module_id = $proc->id;
            $fprints->version = $proc->version;
            $fprints->finger_print = $finger['finger'];
            $fprints->save();
            return TRUE;
        }
        return $finger['flg_white'] === ProgramModule::FLG_BLACK;
    }

    private function loadModule(&$cache, $modname, int $hid) {
        if (isset($cache[$hid][$modname])) {
            return $cache[$hid][$modname];
        }
        if (!array_key_exists($hid, $cache)) {
            $cache[$hid] = [];
        }
        $pmod = ProgramModule::where('name', $modname)
            ->first();
        $cache[$hid][$modname] = $pmod;
        return $pmod;
    }
}
