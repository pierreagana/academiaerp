<?php

namespace App\Modules\Canteen\Application\Services;

use App\Models\User;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Canteen\Domain\Models\CanteenEnrollmentRequest;
use App\Support\Notifications\NotificationDispatcher;

/**
 * Single source of truth for the canteen enrollment request/approval
 * workflow. Every student already has a `canteen_accounts` wallet
 * (auto-created regardless — see SyncRosterUseCase), so unlike Transport
 * there's no existing pivot to reuse: the latest request row per student
 * IS the enrollment signal.
 */
class CanteenEnrollmentService
{
    public function __construct(private NotificationDispatcher $notifications)
    {
    }

    public function requestEnrollment(Student $student, ?ParentAccount $parent = null): CanteenEnrollmentRequest
    {
        return CanteenEnrollmentRequest::create([
            'student_id' => $student->id,
            'status' => CanteenEnrollmentRequest::STATUS_PENDING,
            'source' => CanteenEnrollmentRequest::SOURCE_PARENT,
            'requested_by_parent_id' => $parent?->id,
        ]);
    }

    public function approve(CanteenEnrollmentRequest $request, User $reviewer): void
    {
        $request->update([
            'status' => CanteenEnrollmentRequest::STATUS_APPROVED,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $student = $request->student;
        if ($student) {
            $this->notifications->notifyStudentGuardians(
                $student, 'canteen', 'Inscription cantine approuvée',
                "L'inscription à la cantine de {$student->first_name} a été approuvée."
            );
        }
    }

    public function reject(CanteenEnrollmentRequest $request, User $reviewer, ?string $reason = null): void
    {
        $request->update([
            'status' => CanteenEnrollmentRequest::STATUS_REJECTED,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $student = $request->student;
        if ($student) {
            $this->notifications->notifyStudentGuardians(
                $student, 'canteen', 'Inscription cantine refusée',
                "L'inscription à la cantine de {$student->first_name} a été refusée" . ($reason ? " ({$reason})." : '.')
            );
        }
    }

    /** Removes an already-approved student from the canteen (school-initiated) — meals/scans are refused right away since isEnrolled() only reads the latest row. */
    public function withdraw(CanteenEnrollmentRequest $request, User $staff): void
    {
        $request->update([
            'status' => CanteenEnrollmentRequest::STATUS_WITHDRAWN,
            'reviewed_by_user_id' => $staff->id,
            'reviewed_at' => now(),
        ]);
    }

    public function directlyEnroll(Student $student, User $staff): CanteenEnrollmentRequest
    {
        return CanteenEnrollmentRequest::create([
            'student_id' => $student->id,
            'status' => CanteenEnrollmentRequest::STATUS_APPROVED,
            'source' => CanteenEnrollmentRequest::SOURCE_SCHOOL,
            'reviewed_by_user_id' => $staff->id,
            'reviewed_at' => now(),
        ]);
    }

    public function isEnrolled(int $studentId): bool
    {
        return $this->latestRequestFor($studentId)?->status === CanteenEnrollmentRequest::STATUS_APPROVED;
    }

    public function latestRequestFor(int $studentId): ?CanteenEnrollmentRequest
    {
        return CanteenEnrollmentRequest::where('student_id', $studentId)->latest()->first();
    }

    /** Informational headcount — no capacity cap, just visibility for the school. */
    public function activeCount(int $schoolId): int
    {
        return CanteenEnrollmentRequest::where('status', CanteenEnrollmentRequest::STATUS_APPROVED)
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('canteen_enrollment_requests')
                    ->groupBy('student_id');
            })
            ->count();
    }
}
