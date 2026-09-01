<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\Tenancy\BelongsToSchool;

class Staff extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $table = 'school_staff';

    public const CONTRACT_TYPES = [
        'cdi' => 'CDI',
        'cdd' => 'CDD',
        'prestataire' => 'Prestataire',
    ];

    protected $casts = [
        'contract_end_date' => 'date',
    ];

    protected $fillable = [
        'school_id',
        'branch_id',
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'role',
        'department',
        'contract_type',
        'contract_end_date',
        'status',
        'hire_date',
        'photo_path',
        'salary',
    ];

    public function portalUser()
    {
        return $this->hasOne(\App\Models\User::class, 'staff_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
