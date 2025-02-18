<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProgramModule extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'hostname_id', 'alarm',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public function hostname() {
        return $this->belongsTo(Hostname::class);
    }

    public function module_logs() {
        return $this->hasMany(ModuleLog::class);
    }
   
    // 最新のログを返す
    public function getLatestLogId() {
        $id = $this->id;
        $log_f = ModuleLog::join('finger_prints as f', 'module_logs.finger_print_id', 'f.id')
           ->where('f.program_module_id', $id)
           ->orderBy('module_logs.id', 'desc')
	   ->select('module_logs.id')
	   ->first();
	$log_f = ($log_f) ? $log_f->id : 0;
	$log_g = ModuleLog::join('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
           ->join('graphs as g', 'g.id', 'gm.graph_id')
           ->where(function($q) use ($id) {
               $q->where('g.parent_id', $id);
           })
	   ->orderBy('module_logs.id', 'desc')
           ->first();
	$log_g = ($log_g) ? $log_g->id : 0;
	$id = $log_f ? ($log_g ? max($log_f, $log_g) : $log_f) : $log_g;
	if ($id) {
            return ModuleLog::find($id);
        }
        return null;
    }

    // モジュールの状態を返す
    public function getStatus() {
        return $this->alarm;
    }
}
