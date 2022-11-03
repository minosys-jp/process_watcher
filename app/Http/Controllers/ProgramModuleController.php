<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hostname;
use App\Models\ProgramModule;
use App\Models\Graph;
use App\Models\FingerPrint;

class ProgramModuleController extends Controller
{
    //
    public function sha_history($modid) {
        $shas = FingerPrint::select('finger_prints.id', 'pm.name', 'pm.flg_white', 'finger_prints.version', 'finger_print', 'finger_prints.created_at')
            ->join('program_modules as pm', function($qq) {
                return $qq->on('pm.id', 'finger_prints.program_module_id');
            })
            ->join('program_modules as pm2', function($qq) {
                return $qq->on('pm.hostname_id', 'pm2.hostname_id')
                    ->on('pm.name', 'pm2.name');
            })
            ->where('pm2.id', $modid)
            ->orderBy('finger_prints.id', 'desc')
            ->paginate(50);
        return view('modules.sha_history')->with(compact('shas'));
    }

    public function graph_history($modid) {
        $module = ProgramModule::find($modid);
        $parents = Graph::select('graphs.id', 'parent_id', 'parent_version', 'graphs.created_at')
            ->join('program_modules as pm2', function($qq) {
                return $qq->on('pm2.id', 'parent_id');
            })
            ->where('pm2.hostname_id', $module->hostname_id)
            ->where('pm2.name', $module->name)
            ->orderBy('graphs.id', 'desc')
            ->paginate(50);
        return view('modules.graph_history')->with(compact('parents'));
    }

    public function child_history($parentid) {
        $children = Graph::select('child_id', 'child_version', 'created_at')
            ->where('parent_id', $parentid)
            ->get();
        return view('modules.child_history')->with(compact('children'));
    }

    public function dll_history($dllid) {
        $module = ProgramModule::find($dllid);
        $children = Graph::select('child_id', 'child_version', 'graphs.created_at')
            ->join('program_modules as pm2', function($qq) {
                return $qq->on('pm2.id', 'child_id');
            })
            ->where('pm2.hostname_id', $module->hostname_id)
            ->where('pm2.name', $module->name)
            ->orderBy('graphs.id', 'desc')
            ->distinct()
            ->get();
        return view('modules.dll_history')->with(compact('children'));
    }

    public function exe_history($dllid) {
        $parents = Graph::select('parent_id', 'parent_version', 'created_at')
            ->where('child_id', $dllid)
            ->get();
        return view('modules.exe_history')->with(compact('parents'));
    }
}
