<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Tenancy\BelongsToSchool;

class Branch extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $table = 'branches';

    protected $fillable = [
        'school_id', 'name', 'code', 'type', 'sector', 'status', 'language_regime', 'levels',
        'city', 'country', 'contact_email', 'contact_phone', 'address', 'latitude', 'longitude',
        'slogan', 'logo_path', 'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'levels' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public const LEVELS = ['Maternelle', 'Primaire', 'Collège', 'Lycée'];

    public function school()
    {
        return $this->belongsTo(\App\Modules\SuperAdmin\Domain\Models\School::class);
    }

    /** The real "Responsable" mechanism — a user assigned to this branch whose role carries the branch-director flag. No duplicate name field on Branch itself. */
    public function director()
    {
        return \App\Models\User::where('current_branch_id', $this->id)
            ->whereHas('assignedRole', fn ($q) => $q->where('is_branch_director', true))
            ->first();
    }

    public function facilitiesList()
    {
        return $this->belongsToMany(\App\Modules\SuperAdmin\Domain\Models\Facility::class, 'branch_facility')->withTimestamps();
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
