<?php

namespace App\Modules\Finance\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipGrade extends Model
{
    protected $fillable = [
        'scholarship_id',
        'period',
        'average',
        'recorded_at',
    ];

    protected $casts = [
        'average' => 'decimal:2',
        'recorded_at' => 'date',
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
