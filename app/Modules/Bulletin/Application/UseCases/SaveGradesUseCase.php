<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Bulletin\Application\DTOs\SaveGradesDTO;
use App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface;

class SaveGradesUseCase
{
    public function __construct(private BulletinGradeRepositoryInterface $repository) {}

    /**
     * @param SaveGradesDTO $dto entries shape: student_id => evaluation_type_id => ['score' => .., 'remark' => ..]
     * Each submitted entry is always a NEW grade row — several entries of the same
     * evaluation type across separate saves are valid (e.g. several interrogations),
     * this never overwrites a previously saved score.
     */
    public function execute(SaveGradesDTO $dto): int
    {
        $saved = 0;

        foreach ($dto->entries as $studentId => $typeEntries) {
            foreach ($typeEntries as $evaluationTypeId => $entry) {
                if (!isset($entry['score']) || $entry['score'] === '') {
                    continue;
                }

                $this->repository->create([
                    'student_id' => (int) $studentId,
                    'subject_id' => $dto->subjectId,
                    'semester_id' => $dto->semesterId,
                    'evaluation_type_id' => (int) $evaluationTypeId,
                    'teacher_id' => $dto->teacherId,
                    'score' => (float) $entry['score'],
                    'remark' => $entry['remark'] ?? null,
                ]);

                $saved++;
            }
        }

        return $saved;
    }
}
