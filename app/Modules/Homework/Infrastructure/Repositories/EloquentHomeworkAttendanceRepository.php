<?php

namespace App\Modules\Homework\Infrastructure\Repositories;

use App\Modules\Homework\Domain\Models\HomeworkAttendance;
use App\Modules\Homework\Domain\Repositories\HomeworkAttendanceRepositoryInterface;

class EloquentHomeworkAttendanceRepository implements HomeworkAttendanceRepositoryInterface
{
    public function forAssignment(int $assignmentId)
    {
        return HomeworkAttendance::where('homework_assignment_id', $assignmentId)->get()->keyBy('student_id');
    }

    public function upsert(int $assignmentId, int $studentId, array $data)
    {
        return HomeworkAttendance::updateOrCreate(
            ['homework_assignment_id' => $assignmentId, 'student_id' => $studentId],
            $data
        );
    }
}
