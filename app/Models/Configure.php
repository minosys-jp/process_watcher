<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configure extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanent_id', 'domain_id', 'ckey', 'cvalue', 'cnum',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    private static $singleton_str = [];
    private static $singleton_num = [];

    private function loadStr($tenant) {
        $conf = $this->whereNotNull('cvalue');
        if ($tenant === null) {
            $conf = $conf->whereNull('tenant_id');
        } else {
            $conf = $conf->where('tenant_id', $tenant);
        }
        return $conf->pluck('cvalue', 'ckey');
    }

    private function loadNum($tenant) {
        $conf = $this->whereNotNull('cnum');
        if ($tenant === null) {
            $conf = $conf->whereNull('tenant_id');
        } else {
            $conf = $conf->where('tenant_id', $tenant);
        }
        return $conf->pluck('cnum', 'ckey');
    }

    public function getValue($tenant, $key, $def) {
        if (!array_key_exists($tenant, self::$singleton_str)) {
            self::$singleton_str[$tenant] = $this->loadStr($tenant);
        }

        if (self::$singleton_str) {
            if (array_key_exists($key, self::$singleton_str)) {
                return self::$singleton_str[$key];
            }
        }
        return $def;
    }

    public function getNum($tenant, $key, $def) {
        if (!array_key_exists($tenant, self::$singleton_num)) {
            self::$singleton_num[$tenant] = $this->loadNum($tenant);
        }

        if (self::$singleton_num) {
            if (array_key_exists($key, self::$singleton_num)) {
                return self::$singleton_num[$key];
            }
        }
        return $def;
    }
}
