<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Support\Tenancy\BelongsToSchool;

class Contract extends Model
{
    use BelongsToSchool;
    protected $table = 'hr_contracts';

    public const STATUSES = [
        'active' => 'Actif',
        'terminated' => 'Résilié',
    ];

    protected $fillable = [
        'school_id',
        'holder_type',
        'holder_id',
        'contract_type',
        'start_date',
        'end_date',
        'reminder_days_before',
        'reminder_acknowledged_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reminder_acknowledged_at' => 'datetime',
    ];

    public function holder()
    {
        return $this->morphTo();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date !== null && $this->end_date->lt(Carbon::today());
    }

    public function getNeedsReminderAttribute(): bool
    {
        if ($this->status !== 'active' || $this->end_date === null || $this->is_expired || $this->reminder_acknowledged_at !== null) {
            return false;
        }

        // is_expired already false above, so end_date is today or in the future — unsigned diff is safe.
        return Carbon::today()->diffInDays($this->end_date) <= $this->reminder_days_before;
    }
}
