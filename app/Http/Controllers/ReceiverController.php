<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\Receiver;

class ReceiverController extends Controller
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
        if (auth()->user()->tenant_id) {
            $tenant_id = auth()->user()->tenant_id;
            $vars['tenant'] = Tenant::find($tenant_id);
            $vars['receivers'] = Receiver::where('tenant_id', $tenant_id)->paginate(50)->appends(['tenant' => $tenant_id]);
            $vars['tenants'] = Tenant::where('id', $tenant_id)->get();
        } else if ($request->has('tenant')) {
            $vars['tenant'] = Tenant::find($request->tenant);
            $vars['receivers'] = Receiver::where('tenant_id', $request->tenant)->paginate(50)->appends(['tenant' => $request->tenant]);
            $vars['tenants'] = Tenant::get();
        } else {
            $vars['tenants'] = Tenant::get();
        }
        return view('receivers.index')->with($vars);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        if (auth()->user()->tenant_id) {
            $tenants = Tenant::where('id', auth()->user()->tenant_id)->get();
        } else {
            $tenants = Tenant::get();
        }
        return view('receivers.create')->with(compact('tenants'));
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
        if (!auth()->user()->tenant_id || $request->tenant_id == auth()->user()->tenant_id) {
            $receiver = new Receiver;
            $receiver->fill($request->only(['email', 'tenant_id']));
            $receiver->save();
            session()->flash('flashSuccess', '引き続き受信対象ドメインを設定してください');
        } else {
            session()->flash('flashFailure', 'アクセス権がありません');
        }
        return redirect()->route('receiver.edit', $receiver->id);
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
        $receiver = Receiver::find($id);
        if (!$receiver) {
            session()->flash('flashFailure', '受信者が定義されていません');
            return redirect()->route('receiver.index');
        }
        if (auth()->user()->tenant_id && auth()->user()->tenant_id != $receiver->tenant_id) {
            session()->flash('flashFailure', 'アクセス権がありません');
            return redirect()->route('receiver.index');
        }
        $domains = Domain::where('tenant_id', $receiver->tenant_id)->get();
        return view('receivers.edit')->with(compact('receiver', 'domains'));
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
        $receiver = Receiver::find($id);
        if (!$receiver) {
            session()->flash('flashFailure', '受信者が定義されていません');
            return redirect()->route('receiver.index');
        }
        if (auth()->user()->tenant_id && auth()->user()->tenant_id != $request->tenant_id) {
            session()->flash('flashFailure', 'アクセス権がありません');
            return redirect()->route('receiver.index');
        }
        $receiver->fill($request->only(['email']));
        $receiver->save();
        $receiver->domains()->sync($request->domain_id);
        session()->flash('flashSuccess', '受信者を更新しました');
        return redirect()->route('receiver.edit', $id);
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
        $receiver = Receiver::find($id);
        if ($receiver) {
            $receiver->domains()->sync([]);
            $receiver->delete();
        }
        session()->flash('flashSuccess', '受信者を削除しました');
        return redirect()->route('receiver.index');
    }
}
