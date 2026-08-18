<?php

namespace App\Modules\Academic\Domain\Models;

use App\Models\User;
use App\Modules\SuperAdmin\Domain\Models\School;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'is_branch_director',
    ];

    protected $casts = [
        'is_branch_director' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withPivot(['can_show', 'can_create', 'can_edit', 'can_update', 'can_delete']);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
