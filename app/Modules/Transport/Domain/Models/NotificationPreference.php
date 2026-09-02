<?php

namespace App\Modules\Transport\Domain\Models;

use App\Modules\Academic\Domain\Models\ParentAccount;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    /** Fixed threshold used for dropoff proximity — the reference UI has no
     *  distance picker for it, only an on/off toggle. */
    public const DEFAULT_DROPOFF_DISTANCE_M = 500;

    protected $fillable = [
        'parent_id',
        'near_pickup_distance_m',
        'next_stop_is_pickup',
        'bus_arrived_pickup',
        'student_picked_up',
        'student_missed_pickup',
        'near_dropoff_enabled',
        'bus_arrived_dropoff',
    ];

    protected $casts = [
        'near_pickup_distance_m' => 'integer',
        'next_stop_is_pickup' => 'boolean',
        'bus_arrived_pickup' => 'boolean',
        'student_picked_up' => 'boolean',
        'student_missed_pickup' => 'boolean',
        'near_dropoff_enabled' => 'boolean',
        'bus_arrived_dropoff' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentAccount::class, 'parent_id');
    }
}
