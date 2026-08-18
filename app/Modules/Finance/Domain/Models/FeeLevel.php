<?php

namespace App\Modules\Finance\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeLevel extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'tuition' => 'Scolarité',
        'cantine' => 'Cantine',
        'transport' => 'Transport',
    ];

    /**
     * Cantine/transport tariffs are school-wide (not per grade level), unlike
     * tuition. 'level' stays NOT NULL for schema simplicity, so non-tuition
     * rows store this sentinel instead of a real grade level — distinct per
     * type so the existing (school_id, level, academic_year) unique index
     * still lets a school have one cantine AND one transport tariff per year.
     */
    public static function schoolWideLevelFor(string $type): string
    {
        return '__' . $type . '__';
    }

    protected $fillable = [
        'school_id',
        'type',
        'level',
        'academic_year',
        'registration_fee',
        'monthly_fee',
        'installments_count',
        'start_date',
    ];

    protected $casts = [
        'registration_fee' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'installments_count' => 'integer',
        'start_date' => 'date',
    ];

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->registration_fee + ((float) $this->monthly_fee * $this->installments_count);
    }
}
