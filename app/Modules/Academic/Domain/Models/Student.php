<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Tenancy\BelongsToSchool;

class Student extends Model
{
    use SoftDeletes, BelongsToSchool;
    
    protected $fillable = [
        'first_name', 'last_name', 'dob', 'birthplace', 'gender', 'nationality', 'email', 'phone', 'address', 'photo_path',
        'blood_group', 'allergies', 'medical_conditions',
        'academic_class_id', 'academic_year', 'roll_number', 'dossier_number', 'status', 'regime', 'enrollment_type',
        'enrollment_date', 'entry_date', 'school_id', 'branch_id'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'entry_date' => 'date',
    ];

    public const ENROLLMENT_TYPES = [
        'new' => 'Nouveau',
        'returning' => 'Ancien',
        'transferred' => 'Transféré',
    ];

    public const REGIMES = [
        'interne' => 'Interne',
        'externe' => 'Externe',
    ];

    public function documents()
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function disciplinaryRecords()
    {
        return $this->hasMany(StudentDisciplinaryRecord::class);
    }

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian');
    }
}
