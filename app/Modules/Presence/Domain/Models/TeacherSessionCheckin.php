<?php

namespace App\Modules\Presence\Domain\Models;

use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Academic\Domain\Models\Timetable;
use Illuminate\Database\Eloquent\Model;

class TeacherSessionCheckin extends Model
{
    protected $fillable = ['teacher_id', 'timetable_id', 'date', 'checked_in_at', 'late_minutes'];

    protected $casts = [
        'date' => 'date',
        'checked_in_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }
}
