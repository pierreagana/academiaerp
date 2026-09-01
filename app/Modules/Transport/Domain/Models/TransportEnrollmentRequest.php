<?php

namespace App\Modules\Transport\Domain\Models;

use App\Models\User;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;

class TransportEnrollmentRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    /** Was approved, then the school removed the student from the service. Distinct from REJECTED (never was approved). */
    public const STATUS_WITHDRAWN = 'withdrawn';

    public const SOURCE_PARENT = 'parent';
    public const SOURCE_SCHOOL = 'school';

    protected $fillable = [
        'student_id',
        'route_stop_id',
        'period',
        'status',
        'source',
        'requested_by_parent_id',
        'reviewed_by_user_id',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function routeStop()
    {
        return $this->belongsTo(RouteStop::class, 'route_stop_id');
    }

    public function requestedByParent()
    {
        return $this->belongsTo(ParentAccount::class, 'requested_by_parent_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
