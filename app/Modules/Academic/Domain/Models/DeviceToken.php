<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    public const PLATFORMS = ['web', 'android', 'ios'];

    protected $fillable = ['parent_id', 'token', 'platform', 'last_used_at'];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentAccount::class, 'parent_id');
    }
}
