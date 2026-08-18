<?php

namespace App\Modules\Communication\Domain\Models;

use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'student_id',
        'parental_authorization',
        'payment_status',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
