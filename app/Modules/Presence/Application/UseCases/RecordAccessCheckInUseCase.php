<?php

namespace App\Modules\Presence\Application\UseCases;

use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Presence\Domain\Models\AccessLog;
use App\Modules\Presence\Domain\Repositories\AccessLogRepositoryInterface;
use Illuminate\Support\Carbon;

class RecordAccessCheckInUseCase
{
    private $repository;

    public function __construct(AccessLogRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(
        int $schoolId,
        string $scannedCode,
        string $action,
        ?int $accessPointId,
        ?int $branchId = null,
        ?string $clientScanId = null,
        ?Carbon $occurredAt = null
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

        return $this->repository->create([
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
