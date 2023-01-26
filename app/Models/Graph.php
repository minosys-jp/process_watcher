<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Graph extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_id', 'child_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public function parent() {
        return $this->belongsTo(ProgramModule::class, 'parent_id');
    }

    public function child() {
        return $this->belongsTo(ProgramModule::class, 'child_id');
    }

    public function module_logs() {
        return $this->belongsToMany(ModuleLog::class);
    }
}
