<?php

namespace App\Modules\Finance\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseBudget extends Model
{
    protected $fillable = [
        'school_id',
        'category',
        'period',
        'academic_year',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public const PERIODS = [
        'monthly' => 'Mensuel',
        'quarterly' => 'Trimestriel',
        'annual' => 'Annuel',
    ];

    public function getConsumedAttribute(): float
    {
        return (float) Expense::where('school_id', $this->school_id)
            ->where('category', $this->category)
            ->where('status', '!=', 'rejected')
            ->sum('amount');
    }

    public function getRemainingAttribute(): float
    {
        return max((float) $this->amount - $this->consumed, 0);
    }

    public function getPercentageAttribute(): float
    {
        return (float) $this->amount > 0 ? round(($this->consumed / (float) $this->amount) * 100) : 0;
    }
}
