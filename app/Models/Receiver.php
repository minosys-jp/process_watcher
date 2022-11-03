<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receiver extends Model
{
    use HasFactory;
    protected $fillable = [
        'tenant_id', 'email',
    ];

    public function domains() {
        return $this->belongsToMany(Domain::class);
    }
}
