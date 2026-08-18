<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastMessage extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'target_roles',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
