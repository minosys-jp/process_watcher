<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
	Log::debug("request:".(request()->getQueryString()));
        $tenant_id = auth()->user()->tenant_id ?? $request->input('tenant');
        if ($tenant_id) {
            $vars['tenant'] = Tenant::find($tenant_id);
            $vars['domains'] = Domain::where('tenant_id', $tenant_id)->paginate(50)->appends(['tenant' => $tenant_id]);
	    Log::debug("tenant:".$tenant_id.",count:".count($vars['domains']));
            foreach ($vars['domains'] as $d) {
                $domainId = Domain::select('domains.id', DB::RAW('max(program_modules.alarm) AS alarm'))
                    ->join('hostnames', 'hostnames.domain_id', 'domains.id')
                    ->join('program_modules', 'program_modules.hostname_id', 'hostnames.id')
                    ->where('hostnames.domain_id', $d->id)
                    ->groupBy('domains.id')
                    ->first();
                $d->status = $domainId ? $domainId->alarm : \App\Models\ModuleLog::FLG_GRAY;
		Log::debug("status:".$d->status);
            }
        }
        $vars['tenants'] = auth()->user()->tenant_id ? Tenant::where('id', $tenant_id)->get() : Tenant::get();
        $breads = [
            'ホーム' => route('home'),
	    'ドメイン一覧' => route('domain.index'),
        ];
	$vars['breads'] = $breads;
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
        $breads = [
            'ホーム' => route('home'),
            'ドメイン作成' => route('domain.create'),
        ];
        return view('domains.create')->with(compact('tenants', 'breads'));
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
        $request->tenant = auth()->user()->tenant_id ?? $request->query('tenant');
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
        $breads = [
            'ホーム' => route('home'),
	    'ドメイン一覧' => route('domain.index'),
	    'ドメイン編集' => route('domain.edit', $id),
        ];
        return view('domains.edit')->with(compact('domain', 'breads'));
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
