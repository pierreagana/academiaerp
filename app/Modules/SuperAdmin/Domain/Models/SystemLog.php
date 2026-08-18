<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'level',
        'message',
        'context',
        'source',
        'ip_address',
        'user_agent',
        'user_id',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
