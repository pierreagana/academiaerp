<?php

namespace App\Modules\Presence\Domain\Models;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class AttendanceRecord extends Model
{
    use BelongsToSchool;
    protected $table = 'attendance_records';

    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';

    protected $fillable = [
        'school_id', 'branch_id', 'academic_class_id', 'student_id', 'date', 'status', 'recorded_by', 'notes',
        'late_minutes', 'justified',
    ];

    protected $casts = [
        'date' => 'date',
        'justified' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }
}
