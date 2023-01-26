<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProgramModule extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'hostname_id',
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
        $log = ModuleLog::select('module_logs.*')
          ->leftJoin('finger_prints as f', 'module_logs.finger_print_id', 'f.id')
          ->leftJoin('graph_module_log as gm', 'gm.module_log_id', 'module_logs.id')
          ->join('graphs as g' 'g.id', 'gm.graph_id')
          ->leftJoin('program_modules as p1', 'g.parent_id', 'p1.id')
          ->leftJoin('program_modules as p2', 'g.child_id', 'p2.id')
          ->where('f.program_module_id', $this->id)
          ->where('p1.id', $this->id)
          ->where('p2.id', $this->id)
          ->orderBy('module_logs.id', 'desc')
          ->first();
        return $log;
    }

    // モジュールの状態を返す
    public function getStatus() {
        $log = $this->getLatestLogId();
        return $log ? $log->status : ModuleLog::FLG_GRAY;
    }
}
