<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Bulletin\Domain\Models\BulletinSubjectPublication;
use App\Modules\Bulletin\Domain\Repositories\BulletinSubjectPublicationRepositoryInterface;

class PublishSubjectGradesUseCase
{
    public function __construct(private BulletinSubjectPublicationRepositoryInterface $repository) {}

    public function execute(int $academicClassId, int $subjectId, int $semesterId, int $publishedBy)
    {
        return $this->repository->updateStatus($academicClassId, $subjectId, $semesterId, [
            'status' => BulletinSubjectPublication::STATUS_PUBLISHED,
            'published_by' => $publishedBy,
            'published_at' => now(),
        ]);
    }
}
