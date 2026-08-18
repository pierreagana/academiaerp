<?php

namespace App\Modules\Presence\Application\UseCases;

use App\Modules\Presence\Domain\Repositories\AttendanceRecordRepositoryInterface;

class SaveAttendanceUseCase
{
    private $repository;

    public function __construct(AttendanceRecordRepositoryInterface $repository)
    {
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
        }
    }
}
