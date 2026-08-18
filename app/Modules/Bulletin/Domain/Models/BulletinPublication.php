<?php

namespace App\Modules\Bulletin\Domain\Models;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Semester;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BulletinPublication extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PUBLISHED = 'published';

    protected $fillable = ['academic_class_id', 'semester_id', 'status', 'validated_by', 'validated_at', 'published_at'];

    protected $casts = [
        'validated_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
