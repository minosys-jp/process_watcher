<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Domain;

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
