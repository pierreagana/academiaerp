<?php

namespace App\Modules\ReportCard\Application\UseCases;

use App\Modules\ReportCard\Application\DTOs\SaveAssessmentsDTO;
use App\Modules\ReportCard\Domain\Repositories\ReportCardAssessmentRepositoryInterface;

class SaveAssessmentsUseCase
{
    public function __construct(private ReportCardAssessmentRepositoryInterface $repository) {}

    public function execute(SaveAssessmentsDTO $dto): int
    {
        $saved = 0;

        foreach ($dto->levels as $studentId => $level) {
            if (!in_array($level, ['acquis', 'en_cours', 'non_acquis'], true)) {
                continue;
            }

            $this->repository->upsert((int) $studentId, $dto->competencyId, $dto->semesterId, [
                'subject_id' => $dto->subjectId,
                'assessed_by' => $dto->assessedBy,
                'level' => $level,
            ]);

            $saved++;
        }

        return $saved;
    }
}
