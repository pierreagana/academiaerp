<?php

namespace App\Modules\Homework\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    const STATUS_NOT_SUBMITTED = 'non_remis';
    const STATUS_SUBMITTED = 'remis';

    protected $fillable = [
        'homework_assignment_id', 'student_id', 'status', 'file_path', 'score', 'feedback', 'submitted_at', 'graded_at',
    ];

    protected $casts = [
        'score' => 'float',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(HomeworkAssignment::class, 'homework_assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
