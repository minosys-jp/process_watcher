<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Hostname;
use App\Models\ProgramModule;
use App\Models\Graph;
use App\Models\FingerPrint;
use App\Models\ModuleLog;

class ProgramModuleController extends Controller
{
    //
    public function index($hostid) {
        $modules = ProgramModule::select('program_modules.*')
            ->where('hostname_id', $hostid)
            ->join('graphs g', 'g.parent_id', 'program_modules.id')
            ->distinct()
            ->get();
        if (!$modules) {
            abort(404);
        }
        $host = Hostname::find($hostid);
        if (!$host) {
            abort(404);
        }
        if (!auth()->user()->tenant_id || $host->domain->tenant_id != auth()->user()->tenant_id) {
            abort(404);
        }
        return view('modules.index')->with(compact('modules'));
    }

    public function sha_history($modid) {
        $shas = FingerPrint::select('finger_prints.*', 'ml.status')
              ->leftJoin('module_logs as ml', 'finger_prints.id', 'ml.finger_print_id')
              ->where('finger_prints.program_module_id', $modid)
              ->orderBy('finger_prints.id', 'desc')
              ->paginate(50);

        return view('modules.sha_history')->with(compact('shas'));
    }

    public function change_status(Request $req, $modid) {
        $pm = ProgramModule::find($modid);
        if (!$pm) {
            abort(404);
        }
        $logOld = $pm->getLatestLogId();

        // 新規にログを作成する
        $log = new ModuleLog;
        $log->status = $req->status;
        $log->save();
        session()->flash('flashSuccess', '状態を更新しました');
        return redirect()->route('module.sha_history', $modid);
    }

    public function graph_history($modid) {
        $mlogs = ModuleLog::select('module_logs.*')
            ->join('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
            ->join('graphs as g', 'g.id', 'gm.graph_id')
            ->where('g.parent_id', $modid)
            ->orderBy('module_logs.id', 'desc')
            ->paginate(50);
        return view('modules.graph_history')->with(compact('mlogs'));
    }

    public function child_history($mlogid) {
        $graphs = Graph::select('graphs.*')
                ->join('graph_module_log as gm', 'gm.graph_id', 'graphs.id')
                ->join('module_logs as ml', 'ml.id', 'gm.module_log_id')
                ->where('ml.id', $mlogid)
                ->paginate(50);
        return view('modules.child_history')->with(compact('graphs'));
    }
}
