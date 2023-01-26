<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Hostname;
use App\Models\ProgramModule;
use App\Models\Graph;
use App\Models\Configure;
use App\Models\ModuleLog;
use App\Models\FingerPrint;
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
                    if (!array_key_exists('dbid', $finger) || !array_key_exists('name', $finger) || !array_key_exists('finger', $finger)) {
                        continue;
                    }

                    $fpid = $this->updateFingerPrint($finger, $hostname);
                    if ($fpid) {
                        $xtable1[$fpid] = $finger['dbid'];
                        $xtable2[$finger['dbid']] = $fpid;
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
                    $this->updateGraph($cache, $module_exe, $dlls, $xtable2);
                }
            }

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
            if ($nKillBlackProc !== null && $nKillBlackProc->cnum) {
                $rVal['kill_black_processes'] = $this->getBlack2($xtable1);
            }

            DB::commit();
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();
            return response()->json([ false, $e->getMessage() ]);
        }

        return response()->json([ true, $rVal ]);
    }

    // finger print の検出と更新
    private function updateFingerPrint($finger, $hostname) {
        $fprints = null;
        $proc = ProgramModule::select('program_modules.*', 'finger_prints.finger_print', 'finger_prints.id as fp_id')
            ->join('finger_prints', 'finger_prints.program_module_id', 'program_modules.id')
            ->where('program_modules.name', $finger['name'])
            ->where('program_modules.hostname_id', $hostname->id)
            ->whereNull('finger_prints.next_id')
            ->first();
        if ($proc) {
            if ($proc->finger_print === $finger['finger']) {
                return $proc->fp_id;
            }
            $finger = new FingerPrint;
            $finger->program_module_id = $proc->id;
            $finger->finger_print = $finger['finger'];
            $finger->save();
            $status = ModuleLog::FLG_GRAY;
            $fingerOld = null;
            // 新しい Module Log は FLG_GRAY
            if ($proc->fp_id) {
                $fingerOld = FingerPrint::find($proc->fp_id);
                $status = $fingerOld->program_module()->getStatus();
                $fingerOld->next_id = $finger->id;
                $fingerOld->save();
            }

            // finger print の新規登録
            $status = ($status === ModuleLog::FLG_WHITE) ? ModuleLog::FLG_BLACK1 : $status;
            $mlogNew = new ModuleLog;
            $mlogNew->program_module_id = $proc->id;
            $mlogNew->status = $status;
            $mlogNew->finger_print_id = $finger->id;
            $mlogNew->save();
            return $finger->id;
        }

        $proc = new ProgramModule;
        $proc->name = $finger['name'];
        $proc->hostname_id = $hostname->id;
        $proc->save();

        $finger = new FingerPrint;
        $finger->program_module_id = $proc->id;
        $finger->finger_print = $finger['finger'];
        $finger->save();

        $mlog = new ModuleLog;
        $mlog->finger_print_id = $finger->id;
        $mlog->save();

        return $finger->id;
    }

    // graph の検出と更新
    private function updateGraph(&$cache, $exe, $dlls, $xtable2) {
        $logid = ModuleLog::leftJoin('graph_module_log gm', 'gm.module_log_id', 'module_logs.id')
            ->leftJoin('graphs g', 'g.id', 'gm.graph_id')
            ->leftJoin('program_modules p0', 'p0.id', 'g.program_module_id')
            ->leftJoin('program_modules p1', 'p1.id', 'g.program_module_id')
            ->where('p0.id', $exe->id)
            ->where('p1.id', $exe->id)
            ->max('module_logs.id');
        if ($logid) {
            $graphsOld = ModuleLog::find($logid()->graphs()->pluck('graphs.id');
        } else {
            $graphsOld = collect([]);
        }
        $status = $exe->getStatus();
        $bChanged = FALSE;
        $graphs = [];
        foreach ($dlls as $dll_id) {
            $dll = $this->loadModule($cache, $dll_id, $xtable2);
            if (!$dll) {
                continue;
            }
            $oGraph = Graph::where('parent_id', $exe->id)
                    ->where('child_id', $dll->id)->first();
            if (!$oGraph) {
                $oGraph = new Graph;
                $oGraph->parent_id = $exe->id;
                $oGraph->child_id = $dll->id;
                $oGraph->save();
            }
            $graphs[] = $oGraph->id;
        }

        // 差集合が空集合でない場合は新たな ModuleLog を作成する
        if ($graphsOld->diff($graphs)->count() > 0) {
            $mlog = new ModuleLog;
            $mlog->status = ($status == ModuleLog::FLG_WHITE ? ModuleLog::FLG_BLACK1 : $status);
            $mlog->save();
            $mlog->graphs()->sync($graphs);
        }
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

    // BLACK2 に分類されている id を返す
    private function getBlack2($xtable1) {
        $blacks = ModuleLog::where('status', ModuleLog::FLG_BLACK2)->pluck('program_module_id')->toArray();
        $black_ids = array_map(function($d) use ($xtable1) { return $xtable1[$d]; }, array_filter(function($d) use ($xtable1) { return in_array($d, $xtable1); }, $blacks);
        return $black_ids;
    }
}
