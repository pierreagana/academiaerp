<?php

namespace App\Modules\Homework\Domain\Models;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Room;
use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Models\Subject;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Bulletin\Domain\Models\BulletinEvaluationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Support\Tenancy\BelongsToSchool;

class HomeworkAssignment extends Model
{
    use BelongsToSchool;
    const TYPE_HOMEWORK = 'devoir_maison';
    const TYPE_TEST = 'interrogation';

    const LIVE_SCHEDULED = 'scheduled';
    const LIVE_IN_PROGRESS = 'in_progress';
    const LIVE_COMPLETED = 'completed';

    protected $fillable = [
        'school_id', 'branch_id', 'academic_class_id', 'subject_id', 'teacher_id', 'semester_id', 'evaluation_type_id', 'room_id',
        'title', 'type', 'description', 'scheduled_at', 'duration_minutes', 'max_score', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function evaluationType()
    {
        return $this->belongsTo(BulletinEvaluationType::class);
    }

    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    public function attendances()
    {
        return $this->hasMany(HomeworkAttendance::class);
    }

    /** Real live status for an interrogation, derived from started_at + duration_minutes vs now — never stored separately. */
    public function liveStatus(): string
    {
        if (!$this->started_at) {
            return self::LIVE_SCHEDULED;
        }

        if ($this->ended_at) {
            return self::LIVE_COMPLETED;
        }

        $endsAt = $this->duration_minutes ? $this->started_at->copy()->addMinutes($this->duration_minutes) : null;

        if ($endsAt && Carbon::now()->gte($endsAt)) {
            return self::LIVE_COMPLETED;
        }

        return self::LIVE_IN_PROGRESS;
    }
}
