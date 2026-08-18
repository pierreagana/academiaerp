<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'first_name', 'last_name', 'dob', 'gender', 'email', 'photo_path',
        'blood_group', 'allergies', 'medical_conditions',
        'academic_class_id', 'academic_year', 'roll_number', 'status', 'school_id', 'branch_id'
    ];

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
