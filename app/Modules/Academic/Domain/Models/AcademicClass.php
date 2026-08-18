<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicClass extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name', 'level', 'cycle', 'capacity', 'school_id', 'branch_id'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'academic_class_teacher', 'academic_class_id', 'teacher_id')->withTimestamps();
    }

    public function headTeacher()
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'syllabuses', 'academic_class_id', 'subject_id');
    }
}
