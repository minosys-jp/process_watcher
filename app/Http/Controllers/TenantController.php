<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Receiver;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $tenants = Tenant::paginate(50);
        return view('tenants.index')->with(compact('tenants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('tenants.create');
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
        $request->validate([
            'code' => 'required',
            'name' => 'required',
        ]);
        $tenant = new Tenant;
        $tenant->code = $request->code;
        $tenant->name = $request->name;
        $tenant->save();
        session()->flash('flashSuccess', 'テナントを作成しました');
        return redirect()->route('tenants.edit', $tenant->id);
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
        $tenant = Tenant::find($id);
        if (!$tenant) {
            session()->flash('flashFailure', 'テナントが定義されていません');
            return redirect()->route('tenant.index');
        }
        return view('tenants.edit')->with(compact('tenant'));
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
        $tenant = Tenant::find($id);
        if (!$tenant) {
            session()->flash('flashFailure', 'テナントが定義されていません');
            return redirect()->route('tenant.index');
        }
        if ($request->code) {
            $tenant->code = $request->code;
        }
        if ($request->name) {
            $tenant->name = $request->name;
        }
        $tenant->save();
        session()->flash('flashSuccess', 'テナントを更新しました');
        return redirect()->route('tenants.edit', $tenant->id);
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
        Domain::where('tenant_id', $id)->delete();
        Receiver::where('tenant_id', $id)->delete();
        Tenant::where('id', $id)->delete();
        session()->flash('flashSuccess', 'テナントを削除しました');
        return redirect()->route('tenants.index');
    }
}
