<?php

namespace App\Modules\Homework\Application\UseCases;

use App\Modules\Homework\Domain\Repositories\HomeworkAttendanceRepositoryInterface;
use Illuminate\Support\Carbon;

class MarkAttendanceUseCase
{
    public function __construct(private HomeworkAttendanceRepositoryInterface $repository) {}

    public function execute(int $assignmentId, int $studentId, string $status): void
    {
        $this->repository->upsert($assignmentId, $studentId, [
            'status' => $status,
            'marked_at' => Carbon::now(),
        ]);
    }
}
