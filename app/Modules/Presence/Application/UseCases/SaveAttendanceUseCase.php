<?php

namespace App\Modules\Presence\Application\UseCases;

use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\Presence\Domain\Repositories\AttendanceRecordRepositoryInterface;
use App\Support\Notifications\NotificationDispatcher;

class SaveAttendanceUseCase
{
    private $repository;

    public function __construct(
        AttendanceRecordRepositoryInterface $repository,
        private NotificationDispatcher $notifications
    ) {
        $this->repository = $repository;
    }

    public function execute(int $schoolId, int $academicClassId, string $date, array $statuses, ?int $recordedBy, ?int $branchId = null, array $lateMinutes = [], array $justified = []): void
    {
        foreach ($statuses as $studentId => $status) {
            $this->repository->upsert((int) $studentId, [
                'school_id' => $schoolId,
                'branch_id' => $branchId,
                'academic_class_id' => $academicClassId,
                'date' => $date,
                'status' => $status,
                'recorded_by' => $recordedBy,
                'late_minutes' => $status === 'late' ? ($lateMinutes[$studentId] ?? null) : null,
                'justified' => $status === 'absent' ? isset($justified[$studentId]) : null,
            ]);

            // Only absence/lateness is notification-worthy — a "present"
            // status fires for every student, every day, and would just be noise.
            if (in_array($status, [AttendanceRecord::STATUS_ABSENT, AttendanceRecord::STATUS_LATE], true)) {
                $student = Student::find((int) $studentId);
                if ($student) {
                    $title = $status === AttendanceRecord::STATUS_ABSENT ? 'Absence signalée' : 'Retard signalé';
                    $body = $status === AttendanceRecord::STATUS_ABSENT
                        ? "{$student->first_name} a été marqué(e) absent(e) aujourd'hui."
                        : "{$student->first_name} est arrivé(e) en retard aujourd'hui" . (isset($lateMinutes[$studentId]) ? " ({$lateMinutes[$studentId]} min)." : '.');

                    $this->notifications->notifyStudentGuardians($student, 'attendance', $title, $body);
                }
            }
        }
    }
}
