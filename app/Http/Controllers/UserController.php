<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = User::paginate(50);
        $breads = [
            'ホーム' => route('home'),
            '管理者一覧' => route('user.index'),
        ];
        return view('users.index', compact('users', 'breads'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $breads = [
            'ホーム' => route('home'),
            '管理者作成' => route('user.create'),
        ];
        if (!auth()->user()->tenant_id) {
            $tenants = Tenant::all();
            return view('users.create')->with(compact('tenants', 'breads'));
        }
        return view('users.create')->with(compact('breads'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        //
        $user = new User;
        $user->fill($request->only('email', 'name'));
        $user->password = Hash::make($request->password);
        $user->tenant_id = auth()->user()->tenant_id ?? $request->tenant_id;
        $user->flg_admin = empty($user->tenant_id);
        $user->save();
        session()->flash('flashSuccess', '管理者を作成しました');
        return redirect()->route('user.edit', $user->id);
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
        $user = User::find($id);
        if (!$user) {
            session()->flash('flashFailure', 'ユーザが定義されていません');
            return redirect()->route('user.index');
        }
        $breads = [
            'ホーム' => route('home'),
            '管理者一覧' => route('user.index'),
            '管理者編集' => route('user.edit', $id),
        ];
        return view('users.edit')->with(compact('user', 'breads'));
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
        $user = User::find($id);
        if (!$user) {
            session()->flash('flashFailure', 'ユーザが定義されていません');
            return redirect()->route('user.index');
        }
        if ($request->email) {
            $user->email = $request->email;
        }
        if ($request->name) {
            $user->name = $request->name;
        }
        if ($request->password) {
            if ($request->password != $request->password_confirmation) {
                session()->flash('flashFailure', 'パスワードが一致しません');
                return back()->withInputs();
            }
            $user->password = Hash::make($request->password);
        }
        $user->save();
        session()->flash('flashSuccess', '管理者を更新しました');
        return redirect()->route('user.edit', $user->id);
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
        if (User::count() <= 1) {
            session()->flash('flashFailure', "最後のユーザは削除できません");
            return back()->withInputs();
        }
        $user = User::find($id);
        if (!$user) {
            session()->flash('flashFailure', "ユーザが定義されていません");
            return back()->withInputs();
        }
        $user->delete();
        session()->flash('flashSuccess', 'ユーザを削除しました');
        return redirect()->route('user.index');
    }
}
