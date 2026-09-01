<?php

namespace App\Modules\Transport\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Tenancy\BelongsToSchool;

class Bus extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $table = 'transport_buses';

    public const STATUSES = [
        'en_service' => 'En Service',
        'disponible' => 'Disponible',
        'maintenance' => 'Maintenance',
    ];

    protected $fillable = [
        'school_id',
        'bus_number',
        'plate_number',
        'status',
        'capacity',
        'driver_type',
        'driver_id',
        'current_latitude',
        'current_longitude',
        'position_updated_at',
        'active_route_id',
        'active_shift',
        'trip_started_at',
    ];

    protected $casts = [
        'current_latitude' => 'decimal:7',
        'current_longitude' => 'decimal:7',
        'position_updated_at' => 'datetime',
        'trip_started_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->morphTo();
    }

    public function routes()
    {
        return $this->hasMany(Route::class);
    }

    public function activeRoute()
    {
        return $this->belongsTo(Route::class, 'active_route_id');
    }
}
