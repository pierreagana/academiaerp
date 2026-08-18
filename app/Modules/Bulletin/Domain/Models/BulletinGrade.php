<?php

namespace App\Modules\Bulletin\Domain\Models;

use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Subject;
use App\Modules\Academic\Domain\Models\Teacher;
use Illuminate\Database\Eloquent\Model;

class BulletinGrade extends Model
{
    protected $fillable = ['student_id', 'subject_id', 'semester_id', 'teacher_id', 'evaluation_type_id', 'score', 'remark'];

    protected $casts = [
        'score' => 'float',
    ];

    public function evaluationType()
    {
        return $this->belongsTo(BulletinEvaluationType::class, 'evaluation_type_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
