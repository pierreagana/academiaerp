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
    ];
}
