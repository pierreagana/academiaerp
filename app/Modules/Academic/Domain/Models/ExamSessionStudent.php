<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSessionStudent extends Model
{
    protected $fillable = ['exam_session_id', 'student_id', 'is_admitted'];

    protected $casts = [
        'is_admitted' => 'boolean',
    ];

    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
