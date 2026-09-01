<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class PayrollComponent extends Model
{
    use BelongsToSchool;
    protected $table = 'hr_payroll_components';

    public const TYPES = [
        'allocation' => 'Allocation',
        'deduction' => 'Retenue',
    ];

    public const RATE_TYPES = [
        'fixed' => 'Montant Fixe',
        'percentage' => 'Pourcentage',
    ];

    protected $fillable = [
        'school_id',
        'name',
        'type',
        'rate_type',
        'rate_value',
        'enabled',
    ];

    protected $casts = [
        'rate_value' => 'decimal:2',
        'enabled' => 'boolean',
    ];
}
