<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Bulletin\Domain\Models\BulletinPublication;
use App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface;

class PublishClassUseCase
{
    public function __construct(private BulletinPublicationRepositoryInterface $repository) {}

    public function execute(int $academicClassId, int $semesterId)
    {
        return $this->repository->updateStatus($academicClassId, $semesterId, [
            'status' => BulletinPublication::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }
}
