<?php

namespace App\Modules\Transport\Application\Services;

use App\Models\User;
use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Transport\Domain\Models\RouteStop;
use App\Modules\Transport\Domain\Models\TransportEnrollmentRequest;
use App\Support\Notifications\NotificationDispatcher;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for the bus enrollment request/approval workflow.
 * `transport_route_stop_student` (the pivot the rest of the app already
 * reads to know a student's stop) stays the actual "is this student
 * enrolled" signal — it is only ever written here, either by an approval
 * or by a direct school enrollment, never by a raw parent-initiated write.
 */
class TransportEnrollmentService
{
    public function __construct(private NotificationDispatcher $notifications)
    {
    }

    public function requestEnrollment(Student $student, RouteStop $stop, string $period, ?ParentAccount $parent = null): TransportEnrollmentRequest
    {
        return TransportEnrollmentRequest::create([
            'student_id' => $student->id,
            'route_stop_id' => $stop->id,
            'period' => $period,
            'status' => TransportEnrollmentRequest::STATUS_PENDING,
            'source' => TransportEnrollmentRequest::SOURCE_PARENT,
            'requested_by_parent_id' => $parent?->id,
        ]);
    }

    public function approve(TransportEnrollmentRequest $request, User $reviewer): void
    {
        DB::transaction(function () use ($request, $reviewer) {
            $request->update([
                'status' => TransportEnrollmentRequest::STATUS_APPROVED,
                'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $this->attachPivot($request->student_id, $request->route_stop_id, $request->period);
        });

        $student = $request->student;
        if ($student) {
            $busNumber = $this->busNumberFor($request);
            $this->notifications->notifyStudentGuardians(
                $student, 'bus', 'Inscription transport approuvée',
                "L'inscription au transport scolaire de {$student->first_name} a été approuvée"
                    . ($busNumber ? " (matricule {$busNumber})." : '.')
            );
        }
    }

    /** The bus assigned to this request's route, if any — a route can exist without one yet. */
    private function busNumberFor(TransportEnrollmentRequest $request): ?string
    {
        return $request->routeStop?->route?->bus?->bus_number;
    }

    public function reject(TransportEnrollmentRequest $request, User $reviewer, ?string $reason = null): void
    {
        $request->update([
            'status' => TransportEnrollmentRequest::STATUS_REJECTED,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $student = $request->student;
        if ($student) {
            $busNumber = $this->busNumberFor($request);
            $suffix = $reason ? " ({$reason})" : '';
            $suffix .= $busNumber ? " (matricule {$busNumber})." : '.';
            $this->notifications->notifyStudentGuardians(
                $student, 'bus', 'Inscription transport refusée',
                "L'inscription au transport scolaire de {$student->first_name} a été refusée" . $suffix
            );
        }
    }

    /** Removes an already-approved student from the service (school-initiated) — detaches the pivot immediately, so any later scan for that period is refused right away. */
    public function withdraw(TransportEnrollmentRequest $request, User $staff): void
    {
        DB::transaction(function () use ($request, $staff) {
            DB::table('transport_route_stop_student')
                ->where('student_id', $request->student_id)
                ->where('period', $request->period)
                ->delete();

            $request->update([
                'status' => TransportEnrollmentRequest::STATUS_WITHDRAWN,
                'reviewed_by_user_id' => $staff->id,
                'reviewed_at' => now(),
            ]);
        });
    }

    public function directlyEnroll(Student $student, RouteStop $stop, string $period, User $staff): TransportEnrollmentRequest
    {
        return DB::transaction(function () use ($student, $stop, $period, $staff) {
            $request = TransportEnrollmentRequest::create([
                'student_id' => $student->id,
                'route_stop_id' => $stop->id,
                'period' => $period,
                'status' => TransportEnrollmentRequest::STATUS_APPROVED,
                'source' => TransportEnrollmentRequest::SOURCE_SCHOOL,
                'reviewed_by_user_id' => $staff->id,
                'reviewed_at' => now(),
            ]);

            $this->attachPivot($student->id, $stop->id, $period);

            return $request;
        });
    }

    /** True if the pivot table already links this student to a stop for this period — the actual "can board" signal. */
    public function isEnrolled(int $studentId, string $period): bool
    {
        return DB::table('transport_route_stop_student')
            ->where('student_id', $studentId)
            ->where('period', $period)
            ->exists();
    }

    /** Same "can board" signal as isEnrolled(), additionally scoped to a specific route — needed once a bus can have more than one active route in the same period. */
    public function isEnrolledOnRoute(int $studentId, int $routeId, string $period): bool
    {
        return DB::table('transport_route_stop_student')
            ->join('transport_route_stops', 'transport_route_stops.id', '=', 'transport_route_stop_student.route_stop_id')
            ->where('transport_route_stop_student.student_id', $studentId)
            ->where('transport_route_stop_student.period', $period)
            ->where('transport_route_stops.route_id', $routeId)
            ->exists();
    }

    public function latestRequestFor(int $studentId, string $period): ?TransportEnrollmentRequest
    {
        return TransportEnrollmentRequest::where('student_id', $studentId)
            ->where('period', $period)
            ->latest()
            ->first();
    }

    private function attachPivot(int $studentId, int $stopId, string $period): void
    {
        DB::table('transport_route_stop_student')
            ->where('student_id', $studentId)
            ->where('period', $period)
            ->delete();

        DB::table('transport_route_stop_student')->insert([
            'student_id' => $studentId,
            'route_stop_id' => $stopId,
            'period' => $period,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
