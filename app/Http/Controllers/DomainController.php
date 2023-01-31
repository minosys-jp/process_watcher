<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\ModuleLog;

class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $vars = [];
        $tenant_id = auth()->user()->tenant_id ?? $request->tenant;
        if ($tenant_id) {
            $vars['tenant'] = Tenant::find($tenant_id);
            $vars['domains'] = Domain::where('tenant_id', $tenant_id)->paginate(50)->appends(['tenant' => $tenant_id]);
            foreach ($vars['domains'] as $d) {
                $fid = ModuleLog::leftJoin('finger_prints as f', 'f.id', 'module_logs.finger_print_id')
                    ->leftJoin('program_modules as pm', 'pm.id', 'f.program_module_id')
                    ->leftJoin('hostnames as h', 'h.id', 'pm.hostname_id')
                    ->where('h.domain_id', $d->id)
                    ->max('module_logs.id');
                $pid = ModuleLog::leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                    ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
                    ->leftJoin('program_modules as pm', 'pm.id', 'g.parent_id')
                    ->leftJoin('hostnames as h', 'h.id', 'pm.hostname_id')
                    ->where('h.domain_id', $d->id)
                    ->max('module_logs.id');
                $cid = ModuleLog::leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                    ->leftJoin('graphs as g', 'g.id', 'gm.graph_id')
                    ->leftJoin('program_modules as pm', 'pm.id', 'g.child_id')
                    ->leftJoin('hostnames as h', 'h.id', 'pm.hostname_id')
                    ->where('h.domain_id', $d->id)
                    ->max('module_logs.id');
                $id = max($fid, $pid, $cid); 
                if ($id) {
                    $status = ModuleLog::find($id)->status;
                } else {
                    $status = ModuleLog::FLG_GRAY;
                }
                $d->status = $status;
            }
        }
        $vars['tenants'] = auth()->user()->tenant_id ? Tenant::where('id', $tenant_id)->get() : Tenant::get();
        return view('domains.index')->with($vars);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $tenants = auth()->user()->tenant_id ? Tenant::where('id', auth()->user()->tenant_id)->get() : Tenant::get();
        return view('domains.create')->with(compact('tenants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $domain = new Domain;
        $request->tenant = auth()->user()->tenant_id ?? $request->tenant;
        $domain->fill($request->all());
        $domain->save();
        return redirect()->route('domain.edit', $domain->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $domain = Domain::find($id);
        if (!$domain) {
            session()->flash('flashFailure', 'ドメインが定義されていません');
            return redirect()->route('domain.index');
        }
        if (auth()->user()->tenant_id && $domain->tenant_id != auth()->user()->tenant_id) {
            abort(404);
        }
        return view('domains.edit')->with(compact('domain'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $domain = Domain::find($id);
        if (!$domain) {
            session()->flash('flashFailure', 'ドメインが定義されていません');
            return redirect()->route('domain.index');
        }
        if (auth()->user()->tenant_id && $domain->tenant_id != auth()->user()->tenant_id) {
            abort(404);
        }
        $domain->fill($request->only(['code', 'name']));
        $domain->save();
        session()->flash('flashSuccess', 'ドメインを更新しました');
        return redirect()->route('domain.edit', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $domain = Domain::find($id);
        if ($domain && (!auth()->user()->tenant_id || $domain->tenant_id == auth()->user()->tenant_id)) {
            $domain->receivers()->sync();
            $domain->delete();
        }
        session()->flash('flashSuccess', 'ドメインを削除しました');
        return redirect()->route('domain.index');
    }
}
