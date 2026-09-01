<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class StudentClassMovement extends Model
{
    use BelongsToSchool;
    protected $table = 'student_class_movements';

    const TYPE_TRANSFER = 'transfer';
    const TYPE_PROMOTION = 'promotion';

    protected $fillable = [
        'school_id', 'student_id', 'type', 'from_class_id', 'to_class_id',
        'from_academic_year', 'to_academic_year', 'reason', 'moved_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClass()
    {
        return $this->belongsTo(AcademicClass::class, 'from_class_id');
    }

    public function toClass()
    {
        return $this->belongsTo(AcademicClass::class, 'to_class_id');
    }

    public function movedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'moved_by');
    }
}
