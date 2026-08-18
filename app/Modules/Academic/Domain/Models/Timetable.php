<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timetable extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['academic_class_id', 'subject_id', 'teacher_id', 'room_id', 'day_of_week', 'start_time', 'end_time', 'status'];

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
