<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\ModuleLog;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $tenants = auth()->user()->tenant_id ? Tenant::all() : Tenant::where('id', auth()->user()->tenant_id)->get();

        $mtenants = [];
        foreach ($tenants as $tenant) {
            $mlog_f = ModuleLog::select('module_logs.id')
                ->join('finger_prints as f', 'f.id', 'module_logs.finger_print_id')
                ->join('program_modules as pm', 'pm.id', 'f.program_module_id')
                ->join('hostnames as h', 'h.id', 'pm.hostname_id')
                ->join('domains as d', 'd.id', 'h.domain_id')
                ->where('d.tenant_id', $tenant->id)
                ->orderBy('module_logs.id', 'desc')->first();
            $mlog_g = ModuleLog::select('module_logs.id')
                ->join('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
                ->join('graphs as g', 'g.id', 'gm.graph_id')
                ->join('program_modules as pm', 'pm.id', 'g.parent_id')
                ->join('hostnames as h', 'h.id', 'pm.hostname_id')
                ->join('domains as d', 'd.id', 'h.domain_id')
                ->where('d.tenant_id', $tenant->id)
                ->orderBy('module_logs.id', 'desc')->first();
            $id_f = ($mlog_f ? $mlog_f->id : 0);
            $id_g = ($mlog_g ? $mlog_g->id : 0);
            $id = max($id_f, $id_g);
            $mtenants[] = [
                'id' => $tenant->id,
                'name' => $tenant->name, 
                'status' => !$id ? ModuleLog::FLG_GRAY : ModuleLog::find($id)->status,
            ];
        }
        return view('home')->with(compact('mtenants'));
    }
}
