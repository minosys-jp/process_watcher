<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramModule extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'hostname_id', 'version'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public const FLG_BLACK = 1;
    public const FLG_WHITE = 2;

    public function hostname() {
        return $this->belongsTo(Hostname::class);
    }
}
