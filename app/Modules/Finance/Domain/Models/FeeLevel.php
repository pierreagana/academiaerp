<?php

namespace App\Modules\Finance\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Tenancy\BelongsToSchool;

class FeeLevel extends Model
{
    use SoftDeletes, BelongsToSchool;

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
        'monthly_amounts',
        'installments_count',
        'start_date',
    ];

    protected $casts = [
        'registration_fee' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'monthly_amounts' => 'array',
        'installments_count' => 'integer',
        'start_date' => 'date',
    ];

    /** The amount due for the given 1-indexed installment — a custom breakdown entry if set, else the flat monthly_fee. */
    public function installmentAmount(int $installmentNumber): float
    {
        if (!empty($this->monthly_amounts) && array_key_exists($installmentNumber - 1, $this->monthly_amounts)) {
            return (float) $this->monthly_amounts[$installmentNumber - 1];
        }

        return (float) $this->monthly_fee;
    }

    public function getTotalAmountAttribute(): float
    {
        $monthlyTotal = !empty($this->monthly_amounts)
            ? array_sum($this->monthly_amounts)
            : (float) $this->monthly_fee * $this->installments_count;

        return (float) $this->registration_fee + $monthlyTotal;
    }
}
