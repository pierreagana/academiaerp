<?php

namespace App\Modules\Finance\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScholarshipType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'min_average',
        'min_attendance_rate',
        'max_family_income',
        'min_competition_level',
        'required_documents',
        'default_monthly_amount',
    ];

    protected $casts = [
        'min_average' => 'decimal:2',
        'min_attendance_rate' => 'decimal:2',
        'max_family_income' => 'decimal:2',
        'default_monthly_amount' => 'decimal:2',
    ];

    public function scholarships()
    {
        return $this->hasMany(Scholarship::class);
    }

    public function requiredDocumentLabels(): array
    {
        if (empty($this->required_documents)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $this->required_documents)));
    }
}
