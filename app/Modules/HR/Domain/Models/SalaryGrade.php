<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class SalaryGrade extends Model
{
    use BelongsToSchool;
    protected $table = 'hr_salary_grades';

    protected $fillable = [
        'school_id',
        'name',
        'base_salary',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
    ];

    public const MINIMUM_WAGE_CI = 75000;

    public function getIsCompliantAttribute(): bool
    {
        return (float) $this->base_salary >= self::MINIMUM_WAGE_CI;
    }
}
