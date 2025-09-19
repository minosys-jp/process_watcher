<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Configure;

class ConfigureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $vars = [];
        if ($request->has('tenant_id')) {
            $vars['configs'] = Configure::where('tenant_id', $request->query('tenant_id'))->paginate(50);
            $vars['tenant'] = Tenant::find($request->query('tenant_id'));
        }
        $vars['tenants'] = Tenant::get();
        return view('configs.index')->with($vars);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($tenant_id)
    {
        //
        $tenant = Tenant::find($tenant_id);
        $domains = Domain::where('tenant_id', $tenant_id)->pluck('name', 'id');
        return view('configs.create')->with(compact('tenant', 'domains'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $tenant_id)
    {
        //
        $config = new Configure;
        $config->tenant_id = $tenant_id;
        if ($request->has('domain_id')) {
            $config->domain_id = $request->query('domain_id');
        }
        $config->ckey = $request->query('ckey');
        if ($request->has('cvalue')) {
            $config->cvalue = $request->query('cvalue');
        } else {
            $config->cnum = $request->query('cnum');
        }
        $config->save();
        session()->flash('flashSuccess', '設定を保存しました');
        return redirect()->route('config.edit', [$tenant_id, $config->id]);
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
    public function edit($tenant_id, $id)
    {
        //
        $domains = Domain::where('tenant_id', $tenant_id)->pluck('name', 'id');
        $config = Configure::find($id);
        return view('configs.edit')->with(compact('config', 'domains', 'tenant_id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $tenant_id, $id)
    {
        //
        $config = Configure::find($id);
        if ($request->has('domain_id')) {
            $config->domain_id = $request->query('domain_id');
        }
        $config->ckey = $request->query('ckey');
        if ($request->has('cvalue')) {
            $config->cvalue = $request->query('cvalue');
        } else {
            $config->cnum = $request->query('cnum');
        }
        $config->save();
        session()->flash('flashSuccess', '設定を更新しました');
        return redirect()->route('config.edit', [$tenant_id, $id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($tenant_id, $id)
    {
        //
        $config = Configure::find($id);
        $config->delete();
        session()->flash('flashSuccess', '設定を削除しました');
        return redirect()->route('config.index', compact('tenant_id'));
    }
}
