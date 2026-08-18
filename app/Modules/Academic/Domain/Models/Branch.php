<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $table = 'branches';

    protected $fillable = ['school_id', 'name', 'type', 'city', 'country', 'is_main'];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(\App\Modules\SuperAdmin\Domain\Models\School::class);
    }

    public function classes()
    {
        return $this->hasMany(AcademicClass::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
