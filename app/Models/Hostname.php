<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hostname extends Model
{
    use HasFactory;
    protected $fillable = [
        'code', 'name', 'domain_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public function program_modules() {
        return $this->hasMany(ProgramModule::class);
    }

    public function domain() {
        return $this->belongsTo(Domain::class);
    }
}
