<?php

namespace App\Modules\Presence\Application\UseCases;

use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Presence\Domain\Models\AccessLog;
use App\Modules\Presence\Domain\Repositories\AccessLogRepositoryInterface;
use App\Support\Notifications\NotificationDispatcher;
use Illuminate\Support\Carbon;

class RecordAccessCheckInUseCase
{
    private $repository;

    public function __construct(
        AccessLogRepositoryInterface $repository,
        private NotificationDispatcher $notifications
    ) {
        $this->repository = $repository;
    }

    /**
     * $context distinguishes which physical point this scan came from for
     * notification wording — AccessPoint itself has no type/category column
     * to derive this from, so callers pass it explicitly ('ecole' by default,
     * matching the common gate-entry case; handleCanteen() passes 'cantine').
     */
    public function execute(
        int $schoolId,
        string $scannedCode,
        string $action,
        ?int $accessPointId,
        ?int $branchId = null,
        ?string $clientScanId = null,
        ?Carbon $occurredAt = null,
        string $context = 'ecole',
        bool $notify = true
    ): AccessLog {
        // Idempotent replay: the offline-capable scanner app may upload the
        // same queued scan twice (e.g. it never saw the sync response) —
        // return what was already recorded instead of creating a duplicate.
        if ($clientScanId !== null) {
            $existing = AccessLog::where('client_scan_id', $clientScanId)->first();
            if ($existing) {
                return $existing;
            }
        }

        $raw = trim($scannedCode);
        $matricule = str_contains($raw, ':') ? substr($raw, strrpos($raw, ':') + 1) : $raw;

        $holder = $this->resolveHolder($schoolId, $matricule);

        $log = $this->repository->create([
            'school_id' => $schoolId,
            'branch_id' => $branchId,
            'holder_type' => $holder['type'],
            'holder_id' => $holder['id'],
            'scanned_code' => $raw,
            'client_scan_id' => $clientScanId,
            'person_name' => $holder['name'],
            'role_label' => $holder['role'],
            'action' => $action,
            'access_point_id' => $accessPointId,
            'authorized' => $holder['type'] !== null,
            'occurred_at' => $occurredAt ?? Carbon::now(),
        ]);

        if ($notify && $log->authorized && $holder['type'] === 'student') {
            $student = Student::find($holder['id']);
            if ($student) {
                $this->notifyGuardians($student, $context, $action);
            }
        }

        return $log;
    }

    private function notifyGuardians(Student $student, string $context, string $action): void
    {
        if ($context === 'cantine') {
            $title = 'Présence cantine';
            $body = "{$student->first_name} a été enregistré(e) à la cantine.";
        } else {
            $title = $action === 'exit' ? 'Sortie de l\'école' : 'Arrivée à l\'école';
            $body = $action === 'exit'
                ? "{$student->first_name} a quitté l'établissement."
                : "{$student->first_name} est arrivé(e) à l'établissement.";
        }

        $this->notifications->notifyStudentGuardians($student, 'attendance', $title, $body);
    }

    private function resolveHolder(int $schoolId, string $matricule): array
    {
        $student = Student::where('school_id', $schoolId)->where('roll_number', $matricule)->where('status', 'active')->first();
        if ($student) {
            return ['type' => 'student', 'id' => $student->id, 'name' => $student->first_name . ' ' . $student->last_name, 'role' => 'Élève'];
        }

        $teacher = Teacher::where('school_id', $schoolId)->where('employee_id', $matricule)->where('status', 'active')->first();
        if ($teacher) {
            return ['type' => 'teacher', 'id' => $teacher->id, 'name' => $teacher->first_name . ' ' . $teacher->last_name, 'role' => 'Personnel'];
        }

        $staff = Staff::where('school_id', $schoolId)->where('employee_id', $matricule)->where('status', 'active')->first();
        if ($staff) {
            return ['type' => 'staff', 'id' => $staff->id, 'name' => $staff->first_name . ' ' . $staff->last_name, 'role' => 'Personnel'];
        }

        return ['type' => null, 'id' => null, 'name' => 'Inconnu', 'role' => 'Inconnu'];
    }
}
