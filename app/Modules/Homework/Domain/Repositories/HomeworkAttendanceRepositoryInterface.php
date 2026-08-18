<?php

namespace App\Modules\Homework\Domain\Repositories;

interface HomeworkAttendanceRepositoryInterface
{
    public function forAssignment(int $assignmentId);

    public function upsert(int $assignmentId, int $studentId, array $data);
}
