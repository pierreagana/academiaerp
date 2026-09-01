<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class PlanChangeRequest extends Model
{
    use BelongsToSchool;
    protected $fillable = [
        'school_id',
        'requested_package_id',
        'status',
        'requested_by',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function requestedPackage()
    {
        return $this->belongsTo(SaasPackage::class, 'requested_package_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
