<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Hostname;
use App\Models\ProgramModule;
use App\Models\ModuleLog;
use App\Models\Graph;

class HostnameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $did)
    {
        //
        $domain = Domain::find($did);
        $hostnames = Hostname::where('domain_id', $did)->paginate(50);
        foreach ($hostnames as $h) {
            $fid = ModuleLog::leftJoin('finger_prints as f', 'f.id', 'module_logs.finger_print_id')
                 ->leftJoin('program_modules as pm', 'pm.id', 'f.program_module_id')
                 ->leftJoin('hostnames as h', 'h.id', 'pm.hostname_id')
                 ->where('h.domain_id', $did)
                 ->max('module_logs.id');
            $pid = ModuleLog::leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                 ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
                 ->leftJoin('program_modules as pm', 'pm.id', 'g.parent_id')
                 ->leftJoin('hostnames as h', 'h.id', 'pm.hostname_id')
                 ->where('h.domain_id', $did)
                 ->max('module_logs.id');
            $cid = ModuleLog::leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                 ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
                 ->leftJoin('program_modules as pm', 'pm.id', 'g.child_id')
                 ->leftJoin('hostnames as h', 'h.id', 'pm.hostname_id')
                 ->where('h.domain_id', $did)
                 ->max('module_logs.id');
            $id = max($fid, $pid, $cid);
            if ($id) {
                $status = ModuleLog::find($id)->status;
            } else {
                $status = ModuleLog::FLG_GRAY;
            }
            $h->status = $status;
        }
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
            'ホスト一覧' => route('hostname.index', $did),
        ];
        return view('hostnames.index')->with(compact('hostnames', 'breads'));
    }

    /**
     * ホストの詳細表示
     */
    public function show(Request $request, $hid) {
	$search = $request->search;
        $hostname = Hostname::find($hid);
        if (!$hostname) {
            abort(404);
        }
        $sub = Graph::select('parent_id', DB::raw('min(child_id)'))
            ->groupBy('parent_id')
            ->getQuery();
        $modules = ProgramModule::select('program_modules.*')
                 ->joinSub($sub, 'g', 'g.parent_id', 'program_modules.id')
                 ->where('hostname_id', $hid);
        if ($search) {
            $modules = $modules->where('name', 'like', "%$search%");
        }
        $modules = $modules->paginate(50);
        foreach ($modules as $pm) {
            $f_mid = ModuleLog::leftJoin('finger_prints as f', 'f.id', 'module_logs.finger_print_id')
              ->where('f.program_module_id', $pm->id)
              ->max('module_logs.id');
            $p_mid = ModuleLog::leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                   ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
                   ->where('g.parent_id', $pm->id)
                   ->max('module_logs.id');
            $c_mid = ModuleLog::leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                   ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
                   ->where('g.child_id', $pm->id)
                   ->max('module_logs.id');
            $id = max($f_mid, $p_mid, $c_mid);
            if ($id) {
                $status = ModuleLog::find($id)->status;
            } else {
                $status = ModuleLog::FLG_GRAY;
            }
            $pm->status = $status;
        }
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
	    'ホスト一覧' => route('hostname.index', $hostname->domain_id),
	    'モジュール一覧' => route('hostname.show', $hid),
        ];
        return view('hostnames.show')->with(compact('hostname', 'modules', 'search', 'breads'));
    }

    /**
     * ホスト名の編集
     */
    public function edit($hid) {
        $hostname = Hostname::find($hid);
        if (!$hostname) {
            abort(404);
        }
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
	    'ホスト一覧' => route('hostname.index', $hostname->domain_id),
	    'ホスト編集' => route('hostname.edit', $hid),
        ];
        return view('hostnames.edit')->with(compact('hostname', 'breads'));
    }

    /**
     * ホスト名の編集
     */
    public function update(Request $request, $hid) {
        $hostname = Hostname::find($hid);
        if (!$hostname) {
            abort(404);
        }
        $hostname->fill($request->only(['name']));
        $hostname->save();
        session()->flash('flashSuccess', 'ホスト名を更新しました');
        return redirect()->route('hostname.edit', $hid);
    }
}
