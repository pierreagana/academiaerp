<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Tenancy\BelongsToSchool;

class Scholarship extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id',
        'scholarship_type_id',
        'monthly_amount',
        'status',
        'declared_average',
        'priority',
        'awarded_date',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'declared_average' => 'decimal:2',
        'priority' => 'boolean',
        'awarded_date' => 'date',
        'expiry_date' => 'date',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'active' => 'Actif',
        'suspended' => 'Suspendu',
        'rejected' => 'Rejeté',
        'expired' => 'Expiré',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function type()
    {
        return $this->belongsTo(ScholarshipType::class, 'scholarship_type_id');
    }

    public function disbursements()
    {
        return $this->hasMany(ScholarshipDisbursement::class);
    }

    public function documents()
    {
        return $this->hasMany(ScholarshipDocument::class);
    }

    public function grades()
    {
        return $this->hasMany(ScholarshipGrade::class)->orderBy('recorded_at');
    }

    public function getAnnualAmountAttribute(): float
    {
        return (float) $this->monthly_amount * 12;
    }
}
