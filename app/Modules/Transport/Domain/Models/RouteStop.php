<?php

namespace App\Modules\Transport\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class RouteStop extends Model
{
    use BelongsToSchool;
    protected $table = 'transport_route_stops';

    protected $fillable = [
        'school_id',
        'route_id',
        'sequence',
        'name',
        'address',
        'arrival_time',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'transport_route_stop_student')->withPivot('period');
    }
}
