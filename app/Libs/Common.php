<?php

namespace App\Libs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ModuleLog;
use App\Models\ProgramModule;

class Common {
    static public function expandBreads($breads) {
        $s = '';
        foreach ($breads as $key => $url) {
	    if (!empty($s)) {
                $s .= " &gt; ";
            }
            $s .= "<a href='$url'>$key</a>";
        }
        return $s;
    }

    public function change_status(Request $req, $pm) {
        $logOld = $pm->getLatestLogId();

        // 新規にログを作成する
        $log = new ModuleLog;
        $log->status = $req->status;
        if ($logOld && $logOld->finger_print_id) {
            $log->finger_print_id = $logOld->finger_print_id;
        }
        $log->save();
        if ($logOld && !$logOld->finger_print_id) {
              $graph_ids = DB::table('graph_module_log')
              ->where('module_log_id', $logOld->id)
              ->pluck('graph_id')->toArray();
              $log->graphs()->sync($graph_ids);
        }
        $pm->alarm = $req->status;
        $pm->save();
    }
}
