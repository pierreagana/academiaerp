<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    public const CONTRACT_TYPES = [
        'cdi' => 'CDI',
        'cdd' => 'CDD',
        'prestataire' => 'Prestataire',
    ];

    protected $casts = [
        'contract_end_date' => 'date',
    ];

    protected $fillable = [
        'school_id',
        'branch_id',
        'first_name',
        'last_name',
        'employee_id',
        'email',
        'phone',
        'employment_type',
        'contract_type',
        'contract_end_date',
        'status',
        'photo_path',
        'address',
        'hire_date',
        'department',
        'role',
        'salary',
        'login_id',
        'password',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher');
    }

    public function classes()
    {
        return $this->belongsToMany(AcademicClass::class, 'academic_class_teacher', 'teacher_id', 'academic_class_id')->withTimestamps();
    }

    public function headTeacherClasses()
    {
        return $this->hasMany(AcademicClass::class, 'head_teacher_id');
    }

    public function teachesSubject(int $subjectId): bool
    {
        return $this->subjects->contains($subjectId);
    }

    public function isHeadTeacherOf(int $classId): bool
    {
        return $this->headTeacherClasses->contains($classId);
    }

    public function portalUser()
    {
        return $this->hasOne(\App\Models\User::class, 'teacher_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
