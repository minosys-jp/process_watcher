<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ModuleLog extends Model
{
    use HasFactory;
    protected $fillable = [
         'status', 'finger_print_id', 'flg_noticed', 'flg_discord',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public const FLG_GRAY = 1;
    public const FLG_WHITE = 2;
    public const FLG_BLACK1 = 3;
    public const FLG_BLACK2 = 4;
    public const FLG_NAMES = [ self::FLG_GRAY => 'G', self::FLG_WHITE => 'W', self::FLG_BLACK1 => 'B1', self::FLG_BLACK2 => 'B2' ];

    public function finger_print() {
        return $this->belongsTo(FingerPrint::class);
    }

    public function graphs() {
        return $this->belongsToMany(Graph::class);
    }
}
