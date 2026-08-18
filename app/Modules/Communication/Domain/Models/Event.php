<?php

namespace App\Modules\Communication\Domain\Models;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Room;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'title',
        'type',
        'organizer_name',
        'description',
        'start_at',
        'end_at',
        'location_type',
        'room_id',
        'external_address',
        'audience_type',
        'estimated_budget',
        'equipment_needs',
        'catering_notes',
        'participation_fee',
        'bus_count',
        'bus_capacity',
        'bus_status',
        'catering_count',
        'catering_status',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'equipment_needs' => 'array',
        'estimated_budget' => 'decimal:2',
        'participation_fee' => 'decimal:2',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicClasses()
    {
        return $this->belongsToMany(AcademicClass::class, 'event_academic_class');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'event_teacher')->withTimestamps();
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }
}
