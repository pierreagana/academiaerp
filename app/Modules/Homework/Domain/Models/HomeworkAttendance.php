<?php

namespace App\Modules\Homework\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;

class HomeworkAttendance extends Model
{
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';

    protected $fillable = ['homework_assignment_id', 'student_id', 'status', 'marked_at'];

    protected $casts = [
        'marked_at' => 'datetime',
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
