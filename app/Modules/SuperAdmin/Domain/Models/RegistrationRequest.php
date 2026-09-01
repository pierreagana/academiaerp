<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'school_name',
        'applicant_name',
        'email',
        'phone',
        'region',
        'status',
        'plan_requested',
        'notes',
        'type',
        'sector',
        'language_regime',
        'levels',
        'students_count',
        'slogan',
        'city',
        'country',
        'address',
        'latitude',
        'longitude',
        'logo_path',
        'facilities',
    ];

    protected $casts = [
        'levels' => 'array',
        'facilities' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];
}
