<?php

namespace App\Modules\Transport\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class Route extends Model
{
    use BelongsToSchool;
    protected $table = 'transport_routes';

    public const STATUSES = [
        'actif' => 'Actif',
        'planifie' => 'Planifié',
        'inactif' => 'Inactif',
    ];

    public const SCHEDULE_TYPES = [
        'recurring' => 'Répété automatiquement',
        'occasional' => 'Voyage occasionnel',
    ];

    public const DAYS = [
        'mon' => 'Lun',
        'tue' => 'Mar',
        'wed' => 'Mer',
        'thu' => 'Jeu',
        'fri' => 'Ven',
        'sat' => 'Sam',
        'sun' => 'Dim',
    ];

    /** Null (absent from this map) means "both periods" — see the period column's own comment. */
    public const PERIODS = [
        'morning' => 'Matin',
        'evening' => 'Soir',
    ];

    protected $fillable = [
        'school_id',
        'name',
        'zone',
        'price',
        'distance_km',
        'bus_id',
        'status',
        'period',
        'first_stop_time',
        'stop_interval_minutes',
        'schedule_type',
        'recurring_days',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'recurring_days' => 'array',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class)->orderBy('sequence');
    }
}
