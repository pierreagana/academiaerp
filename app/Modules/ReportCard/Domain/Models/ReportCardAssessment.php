<?php

namespace App\Modules\ReportCard\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Subject;
use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Models\Teacher;
use Illuminate\Database\Eloquent\Model;

class ReportCardAssessment extends Model
{
    protected $table = 'report_card_assessments';

    const LEVEL_ACQUIS = 'acquis';
    const LEVEL_EN_COURS = 'en_cours';
    const LEVEL_NON_ACQUIS = 'non_acquis';

    protected $fillable = ['student_id', 'competency_id', 'semester_id', 'subject_id', 'level', 'assessed_by'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function competency()
    {
        return $this->belongsTo(ReportCardCompetency::class, 'competency_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function assessedBy()
    {
        return $this->belongsTo(Teacher::class, 'assessed_by');
    }
}
