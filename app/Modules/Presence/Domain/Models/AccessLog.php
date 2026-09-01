<?php

namespace App\Modules\Presence\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class AccessLog extends Model
{
    use BelongsToSchool;
    protected $table = 'access_logs';

    const ACTION_ENTRY = 'entry';
    const ACTION_EXIT = 'exit';

    protected $fillable = [
        'school_id', 'branch_id', 'holder_type', 'holder_id', 'scanned_code', 'client_scan_id', 'person_name',
        'role_label', 'action', 'access_point_id', 'authorized', 'occurred_at',
    ];

    protected $casts = [
        'authorized' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public function holder()
    {
        return $this->morphTo();
    }

    public function accessPoint()
    {
        return $this->belongsTo(AccessPoint::class);
    }
}
