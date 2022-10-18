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
        'program_module_id', 'version', 'algorithm_id', 'finger_print',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
}
