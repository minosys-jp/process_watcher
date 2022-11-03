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
        if ($request->has('tenant')) {
            $vars['tenant'] = Tenant::find($request->tenant);
            $vars['domains'] = Domain::where('tenant_id', $request->tenant)->paginate(50)->appends(['tenant' => $request->tenant]);
        }
        $vars['tenants'] = Tenant::get();
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
        $tenants = Tenant::get();
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
        if ($domain) {
            $domain->receivers()->sync();
            $domain->delete();
        }
        session()->flash('flashSuccess', 'ドメインを削除しました');
        return redirect()->route('domain.index');
    }
}
