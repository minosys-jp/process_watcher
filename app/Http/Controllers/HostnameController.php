<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Hostname;
use App\Models\ProgramModule;

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
        return view('hostnames.index')->with(compact('hostnames'));
    }

    /**
     * ホストの詳細表示
     */
    public function show($hid) {
        $hostname = Hostname::find($hid);
        if (!$hostname) {
            abort(404);
        }
        $modules = ProgramModule::select('program_modules.*')
            ->joinSub(ProgramModule::select('name', DB::raw('max(version) as version'))
                    ->groupBy('name'), 'pm2', function($join) {
                        $join->on('program_modules.name', 'pm2.name')
                            ->on('program_modules.version', 'pm2.version');
                    })
            ->where('program_modules.hostname_id', $hid)
            ->paginate(50);
        return view('hostnames.show')->with(compact('hostname', 'modules'));
    }

    /**
     * ホスト名の編集
     */
    public function edit($hid) {
        $hostname = Hostname::find($hid);
        if (!$hostname) {
            abort(404);
        }
        return view('hostnames.edit')->with(compact('hostname'));
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
