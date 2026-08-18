<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Bulletin\Domain\Models\BulletinSubjectPublication;
use App\Modules\Bulletin\Domain\Repositories\BulletinSubjectPublicationRepositoryInterface;

class UnpublishSubjectGradesUseCase
{
    public function __construct(private BulletinSubjectPublicationRepositoryInterface $repository) {}

    public function execute(int $academicClassId, int $subjectId, int $semesterId)
    {
        return $this->repository->updateStatus($academicClassId, $subjectId, $semesterId, [
            'status' => BulletinSubjectPublication::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }
}
