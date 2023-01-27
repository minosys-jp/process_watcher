<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerPrint extends Model
{
    use HasFactory;

    public const ALG_SHA2_256 = 1;
    public const ALG_SHA3_256 = 2;

    protected $fillable = [
        'program_module_id', 'finger_print', 'next_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public function program_module() {
        return $this->belongsTo(ProgramModule::class);
    }

    public function getStatus() {
        $mlog = ModuleLog::where('program_module_id', $this->program_module_id)
              ->whereNull('finger_print_id')
              ->orderBy('id', 'desc')
              ->first();
        if (!$mlog) {
            return ModuleLog::FLG_GRAY;
        }
        $parents = $mlog->graphs()->pluck('graphs.parent_id')->toArray();
        $children = $mlog->graphs()->pluck('graphs.children')->toArray();
        $status = ModuleLog::FLG_GRAY;
        if (count($parents) > 0) {
            $st = ProgramModule::whereIn('id', $parents)->max('status');
            if ($st) {
                $status = max($status, $st);
            }
        }
        if (count($children) > 0) {
            $st = ProgramModule::whereIn('id', $children)->max('status');
            if ($st) {
                $status = max($status, $st);
            }
        }
        return $status;
    }
}
