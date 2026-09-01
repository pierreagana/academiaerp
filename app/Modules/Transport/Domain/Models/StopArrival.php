<?php

namespace App\Modules\Transport\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class StopArrival extends Model
{
    protected $table = 'transport_stop_arrivals';

    protected $fillable = [
        'route_stop_id',
        'route_id',
        'bus_id',
        'driver_id',
        'period',
        'arrived_at',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
    ];

    public function routeStop()
    {
        return $this->belongsTo(RouteStop::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
