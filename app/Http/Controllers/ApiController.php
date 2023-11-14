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
        $tenant_code = trim($request->tenant);
        $domain_code = trim($request->domain);
        $host_code = trim($request->hostname);
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
    
	    // flg_publish の確認
	    $flg_publish = 1;
	    if ($request->has('flg_publish')) {
                $flg_publish = $request->flg_publish;
	    }

            // フィンガープリントの更新
            if ($flg_publish && $fingers && is_array($fingers)) {
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
            if ($flg_publish && $graphs && is_array($graphs)) {
                foreach ($graphs as $graph) {
                    $exe = $graph['exe'];
                    if (!array_key_exists('dlls', $graph)) {
                        continue;
                    }
                    $dlls = $graph['dlls'];
                    $module_exe = $this->loadModule($cache, $exe, $xtable2);
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
	    Log::debug("flg_publish:".$hostname->flg_publish);
	    $rVal['flg_publish'] = ($hostname->flg_publish >= 1) ? 1 : 0;

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
            ->where('program_modules.name', trim($finger['name']))
            ->where('program_modules.hostname_id', $hostname->id)
            ->whereNull('finger_prints.next_id')
            ->first();
        if ($proc) {
            if (empty(trim($finger['finger'])) || $proc->finger_print === $finger['finger']) {
                return $proc->id;
            }
            $fingerNew = new FingerPrint;
            $fingerNew->program_module_id = $proc->id;
            $fingerNew->finger_print = trim($finger['finger']);
            $fingerNew->save();
	    Log::debug("Found ProcId: ". $proc->id . ":" . $finger['finger']);
            $status = ModuleLog::FLG_GRAY;
            $fingerOld = null;
            // 新しい Module Log は FLG_GRAY
            if ($proc->fp_id) {
                $fingerOld = FingerPrint::find($proc->fp_id);
                $status = $fingerOld->program_module->getStatus();
                $fingerOld->next_id = $fingerNew->id;
                $fingerOld->save();
            }

            // finger print の新規登録
            $statusNew = ($status === ModuleLog::FLG_WHITE) ? ModuleLog::FLG_BLACK1 : $status;
            $mlogNew = new ModuleLog;
            $mlogNew->status = $statusNew;
            $mlogNew->finger_print_id = $fingerNew->id;
            $mlogNew->save();
            if ($statusNew > $status) {
                $proc->alarm = $statusNew;
		$proc->save();
            }
            return $proc->id;
        }

        $proc = new ProgramModule;
        $proc->name = trim($finger['name']);
        $proc->hostname_id = $hostname->id;
        $proc->save();

        $oFinger = new FingerPrint;
        $oFinger->program_module_id = $proc->id;
        $oFinger->finger_print = trim($finger['finger']);
	Log::debug($proc->id . ":" . $finger['finger']);
        $oFinger->save();

        $mlog = new ModuleLog;
        $mlog->finger_print_id = $oFinger->id;
        $mlog->save();

        return $proc->id;
    }

    // graph の検出と更新
    private function updateGraph(&$cache, $exe, $dlls, $xtable2) {
        $logid = ModuleLog::leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
            ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
            ->where('g.parent_id', $exe->id)
            ->max('module_logs.id');
	$last_created_at = Carbon::parse("2023-01-01");
        if ($logid) {
	    $mlog = ModuleLog::find($logid);
	    $last_created_at = Carbon::parse($mlog->created_at);
            $graphsOld = $mlog->graphs()->pluck('graphs.id')->toArray();
        } else {
            $graphsOld = [];
        }
        $status = $exe->getStatus();
        $statusNew = $status;
        $bChanged = FALSE;
        $graphs = [];
        foreach ($dlls as $dll_id) {
            $dll = $this->loadModule($cache, $dll_id, $xtable2);
            if (!$dll) {
                if (array_key_exists($dll_id, $xtable2)) {
                    Log::debug("failed to load dll:" . $dll_id . ":" . $xtable2[$dll_id]);
                } else {
                    Log::debug("failed to load dll:" . $dll_id);
		}
                continue;
            }
            $oGraph = Graph::where('parent_id', $exe->id)
                    ->where('child_id', $dll->id)->first();
            if (!$oGraph) {
                $oGraph = new Graph;
                $oGraph->parent_id = $exe->id;
                $oGraph->child_id = $dll->id;
                $oGraph->save();
Log::debug("created new Graph:" . $oGraph->id . ":" . $exe->id . "=>" . $dll->id);
            } else {
                $statusNew = ($dll->alarm > $statusNew) ? $dll->alarm : $statusNew;
            }
            $graphs[] = $oGraph->id;
        }

Log::debug("graphsOld:" . implode(",", $graphsOld));
Log::debug("graphs:" . implode(",", $graphs));
        // 差集合に変化があっても、DLLが健全ならログを作成しない
        if ($statusNew !== $status) {
            $log = new ModuleLog;
            $log->status = $statusNew;
            $log->save();
            $log->graphs()->sync($graphs);
            $exe->alarm = $statusNew;
            $exe->save();

	    // $logid に結合する graphs は削除する
	    if ($logid) {
                $mlog = ModuleLog::find($logid);
                $mlog->graphs()->sync([]);
	    }
	}
    }

    private function checkDiff($graphsOld, $graphs) {
        $sub1 = array_filter($graphsOld, function($e) use ($graphs) { return !in_array($e, $graphs); });
        $sub2 = array_filter($graphs, function($e) use ($graphsOld) { return !in_array($e, $graphsOld); });
Log::debug("sub1:" . implode(",", $sub1). ":" . count($sub1));
Log::debug("sub2:" . implode(",", $sub2). ":" . count($sub2));
        return count($sub1) > 0 || count($sub2) > 0;
    }

    private function loadModule(&$cache, int $modid, $xtable2) {
	    /*
        if (isset($cache[$modid])) {
            return $cache[$modid];
        }
	     */
        $pmod = ProgramModule::where('id', $xtable2[$modid])
            ->first();
        //$cache[$modid] = $pmod;
        return $pmod;
    }

    // BLACK2 に分類されている id を返す
    private function getBlack2($xtable1) {
       $currents = ModuleLog::select(DB::raw('max(module_logs.id) as log_id'),  'pm.id')
            ->leftJoin('finger_prints as f', 'f.id', 'module_logs.finger_print_id')
            ->leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
            ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
	    ->leftJoin('program_modules as pm', function($q) {
		    $q->on('pm.id', 'f.program_module_id')
                      ->orOn('pm.id', 'g.parent_id');
              })
            ->groupBy(['pm.id'])
            ->getQuery();
        $blacks = ModuleLog::leftJoinSub($currents, 'md', 'module_logs.id', 'md.log_id')
            ->where('module_logs.status', ModuleLog::FLG_BLACK2)
            ->pluck('md.id')->toArray();

        $keys = array_keys($xtable1);
        $bf = array_filter($blacks, function($d) use ($keys) {
            return in_array($d, $keys);
        });
	$bf = array_values($bf);
	Log::debug("JSON1:".json_encode($bf));
        $black_ids = array_map(function($d) use ($xtable1) { return $xtable1[$d]; }, $bf);
	Log::debug("JSON2:".json_encode($black_ids));
        return $black_ids;
    }
}
