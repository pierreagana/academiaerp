<?php

namespace App\Modules\Bulletin\Domain\Models;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BulletinSubjectPublication extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    protected $fillable = ['academic_class_id', 'subject_id', 'semester_id', 'status', 'published_by', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
