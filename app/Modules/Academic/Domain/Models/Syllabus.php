<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Syllabus extends Model
{
    use SoftDeletes;
    
    protected $table = 'syllabuses';
    
    protected $fillable = ['academic_class_id', 'semester_id', 'subject_id', 'description', 'file_path'];

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
