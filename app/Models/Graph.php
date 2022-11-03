<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Graph extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_id', 'child_id', 'parent_version', 'child_version',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public function parentModule() {
        return $this->belongsTo(ProgramModule::class, 'parent_id');
    }

    public function childModule() {
        return $this->belongsTo(ProgramModule::class, 'child_id');
    }
}
