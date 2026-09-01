<?php

namespace App\Modules\Transport\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class TripLog extends Model
{
    use BelongsToSchool;
    protected $table = 'transport_trip_logs';

    public const SHIFTS = [
        'matin' => 'Tournée Matinale',
        'soir' => 'Tournée du Soir',
    ];

    public const STATUSES = [
        'complete' => 'Complété',
        'incident' => 'Incident',
    ];

    protected $fillable = [
        'school_id',
        'route_id',
        'bus_id',
        'shift',
        'trip_date',
        'scheduled_start',
        'status',
        'attendance_count',
        'expected_count',
        'distance_km',
        'incident_notes',
        'created_by',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'distance_km' => 'decimal:2',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
}
