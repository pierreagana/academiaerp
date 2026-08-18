<?php

namespace App\Modules\Finance\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipDisbursement extends Model
{
    protected $fillable = [
        'scholarship_id',
        'label',
        'amount',
        'due_date',
        'paid_date',
        'method',
        'reference',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
