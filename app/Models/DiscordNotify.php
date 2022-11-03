<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscordNotify extends Model
{
    use HasFactory;
    protected $fillable = [
        'tenant_id', 'domain_id', 'hostname_id', 'graph_id', 'finger_print_id', 'type_id', 'description'
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public const TYPE_NEW = 1;
    public const TYPE_UPDATE = 2;

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function domain() {
        return $this->belongsTo(Domain::class);
    }

    public function Hostname() {
        return $this->belongsTo(Hostname::class);
    }

    public function graph() {
        return $this->belongsTo(Graph::class);
    }

    public function finger_print() {
        return $this->belongsTo(FingerPrint::class);
    }
}
