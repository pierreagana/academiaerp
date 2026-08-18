<?php

namespace App\Modules\Homework\Application\UseCases;

use App\Modules\Homework\Domain\Models\HomeworkSubmission;
use App\Modules\Homework\Domain\Repositories\HomeworkSubmissionRepositoryInterface;
use Illuminate\Support\Carbon;

class SaveSubmissionsUseCase
{
    public function __construct(private HomeworkSubmissionRepositoryInterface $repository) {}

    /** @param array<int, array{status?: string, score?: ?float, feedback?: ?string, file_path?: ?string}> $entries keyed by student_id */
    public function execute(int $assignmentId, array $entries): void
    {
        $existing = $this->repository->forAssignment($assignmentId);

        foreach ($entries as $studentId => $entry) {
            $status = $entry['status'] ?? HomeworkSubmission::STATUS_NOT_SUBMITTED;
            $previous = $existing->get((int) $studentId);

            $data = [
                'status' => $status,
                'score' => $entry['score'] !== '' && $entry['score'] !== null ? (float) $entry['score'] : null,
                'feedback' => $entry['feedback'] ?: null,
            ];

            if (!empty($entry['file_path'])) {
                $data['file_path'] = $entry['file_path'];
            }

            if ($status === HomeworkSubmission::STATUS_SUBMITTED && !$previous?->submitted_at) {
                $data['submitted_at'] = Carbon::now();
            }

            if ($data['score'] !== null && !$previous?->graded_at) {
                $data['graded_at'] = Carbon::now();
            }

            $this->repository->upsert($assignmentId, (int) $studentId, $data);
        }
    }
}
