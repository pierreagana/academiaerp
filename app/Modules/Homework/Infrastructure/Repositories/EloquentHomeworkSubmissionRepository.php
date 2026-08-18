<?php

namespace App\Modules\Homework\Infrastructure\Repositories;

use App\Modules\Homework\Domain\Models\HomeworkSubmission;
use App\Modules\Homework\Domain\Repositories\HomeworkSubmissionRepositoryInterface;

class EloquentHomeworkSubmissionRepository implements HomeworkSubmissionRepositoryInterface
{
    public function forAssignment(int $assignmentId)
    {
        return HomeworkSubmission::where('homework_assignment_id', $assignmentId)->get()->keyBy('student_id');
    }

    public function upsert(int $assignmentId, int $studentId, array $data)
    {
        return HomeworkSubmission::updateOrCreate(
            ['homework_assignment_id' => $assignmentId, 'student_id' => $studentId],
            $data
        );
    }
}
