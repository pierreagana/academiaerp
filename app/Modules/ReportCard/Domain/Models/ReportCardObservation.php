<?php

namespace App\Modules\ReportCard\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Models\Teacher;
use Illuminate\Database\Eloquent\Model;

class ReportCardObservation extends Model
{
    protected $table = 'report_card_observations';

    protected $fillable = ['student_id', 'teacher_id', 'semester_id', 'comment'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
