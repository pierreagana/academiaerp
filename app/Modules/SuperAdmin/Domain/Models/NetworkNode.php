<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkNode extends Model
{
    use HasFactory;

    protected $table = 'network_nodes';

    protected $fillable = [
        'name',
        'region',
        'ip_address',
        'status',
        'latency_ms',
        'load_pct',
    ];
}
