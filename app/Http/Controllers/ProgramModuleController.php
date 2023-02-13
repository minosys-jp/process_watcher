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
            ->join('modules_logs', 'module_logs.id', 'sub.log_id')
            ->where('hostname_id', $hostid)
            ->join('graphs g', 'g.parent_id', 'program_modules.id')
            ->distinct()
            ->get();
        if (!$modules) {
            abort(404);
        }
        foreach ($modules as $pm) {
            $fmid = ModuleLog::leftJoin('finger_prints as f', 'f.id', 'module_logs.finger_print_id')
                  ->where('f.program_module_id', $pm->id)
                  ->max('module_logs.id');
            $pmid = ModuleLog::leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                  ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
                  ->where('g.parent_id', $pm->id)
                  ->max('module_logs.id');
            $cmid = ModuleLog::leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                  ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
                  ->where('g.child_id', $pm->id)
                  ->max('module_logs.id');
            $id = max($fmid, $pmid, $cmid);
            if ($id) {
                $status = ModuleLog::find($id)->status;
            } else {
                $status = ModuleLog::FLG_GRAY;
            }
            $pm->status = $status;
        }

        $host = Hostname::find($hostid);
        if (!$host) {
            abort(404);
        }
        if (!auth()->user()->tenant_id || $host->domain->tenant_id != auth()->user()->tenant_id) {
            abort(404);
        }
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
            'ホスト一覧' => route('hostname.index', $hosname->domain_id),
            'モジュール一覧' => route('hostname.show', $hostid),
        ];
        return view('modules.index')->with(compact('modules', 'breads'));
    }

    public function sha_history($modid) {
        $shas = FingerPrint::select('finger_prints.*', 'ml.status')
              ->leftJoin('module_logs as ml', 'finger_prints.id', 'ml.finger_print_id')
              ->where('finger_prints.program_module_id', $modid)
              ->orderBy('finger_prints.id', 'desc')
              ->paginate(50);
	$mod = ProgramModule::find($modid);
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
            'ホスト一覧' => route('hostname.index', $mod->hostname->domain_id),
            'モジュール一覧' => route('hostname.show', ['hostname' => $mod->hostname_id]),
        ];
        $flg_parent = Graph::where('parent_id', $modid)->exists();
	if (!$flg_parent) {
            $breads['親グラフ履歴'] = route('module.graph_history', $modid);
	}
        $breads['更新履歴'] = route('module.sha_history', $modid);
        return view('modules.sha_history')->with(compact('shas', 'flg_parent', 'breads'));
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
        $sub = Graph::select(DB::raw('max(graphs.child_id) as child_id'), 'gm.module_log_id as log_id')
            ->join('graph_module_log as gm', 'gm.graph_id', 'graphs.id')
            ->groupBy(['log_id'])->getQuery();
        $mlogs = ModuleLog::select('module_logs.*')
            ->join('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
            ->join('graphs as g', 'g.id', 'gm.graph_id')
            ->joinSub($sub, "sub", function($q) {
                $q->on('sub.log_id', 'module_logs.id')
                  ->on("sub.child_id", "g.child_id");
            })
            ->where('g.parent_id', $modid)
            ->orderBy('module_logs.id', 'desc')
            ->paginate(50);
        $mod = ProgramModule::find($modid);
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
            'ホスト一覧' => route('hostname.index', $mod->hostname->domain_id),
            'モジュール一覧' => route('hostname.show', ['hostname' => $mod->hostname_id]),
	    '親グラフ履歴' => route('module.graph_history', $modid),
        ];
        return view('modules.graph_history')->with(compact('mlogs', 'breads'));
    }

    public function child_history($mlogid) {
        $graphs = Graph::select('graphs.*')
                ->join('graph_module_log as gm', 'gm.graph_id', 'graphs.id')
                ->join('module_logs as ml', 'ml.id', 'gm.module_log_id')
                ->where('ml.id', $mlogid)
                ->paginate(50);
        $parent_id = null;
        foreach ($graphs as $g) {
            $id = ModuleLog::join('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                ->join('graphs as g', 'g.id', 'gm.graph_id')
                ->where('g.id', $g->id)
                ->where('module_logs.id', '<=', $mlogid)
                ->max('module_logs.id');
            if ($id) {
                $status = ModuleLog::find($id)->status;
            } else {
                $status = ModuleLog::FLG_GRAY;
            }
            $g->status = $status;
            $parent_id = $g->parent_id;
        }
        $mod = ProgramModule::find($parent_id);
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
            'ホスト一覧' => route('hostname.index', $mod->hostname->domain_id),
            'モジュール一覧' => route('hostname.show', ['hostname' => $mod->hostname_id]),
	    '親グラフ履歴' => route('module.graph_history', $parent_id),
            '従属 DLL 群' => route('module.child_history', $mlogid),
        ];
        return view('modules.child_history')->with(compact('graphs', 'breads'));
    }
}
