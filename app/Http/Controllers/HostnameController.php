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
use App\Libs\Common;

class HostnameController extends Controller
{
    public $lib;

    public function __construct(Common $lib) {
        $this->lib = $lib;
    }

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
            $hostnameAlarm = Hostname::select('hostnames.id', DB::RAW('max(program_modules.alarm) AS alarm'))
                ->join('program_modules', 'program_modules.hostname_id', 'hostnames.id')
                ->where('hostnames.id', $h->id)
                ->groupBy('hostnames.id')
                ->first();
            $h->status = $hostnameAlarm ? $hostnameAlarm->alarm : \App\Models\ModuleLog::FLG_GRAY;
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
        $breads = [
            'ホーム' => route('home'),
            'ドメイン一覧' => route('domain.index'),
	    'ホスト一覧' => route('hostname.index', $hostname->domain_id),
	    'モジュール一覧' => route('hostname.show', $hid),
        ];
        return view('hostnames.show')->with(compact('hostname', 'modules', 'search', 'breads'));
    }

    /**
     * ホストの状態変更
     */
    public function change(Request $req, $hid) {
        $host = Hostname::find($hid);
        if (!$host) {
            abort(404);
        }
        $modules = $host->program_modules;
        foreach ($modules as $pm) {
            $this->lib->change_status($req, $pm);
        }
        session()->flash('flashSuccess', 'ホストの状態を更新しました');
        return redirect()->route('hostname.show', $hid);
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
