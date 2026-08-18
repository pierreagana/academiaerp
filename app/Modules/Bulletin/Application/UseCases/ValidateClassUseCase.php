<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Bulletin\Domain\Models\BulletinPublication;
use App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface;

class ValidateClassUseCase
{
    public function __construct(private BulletinPublicationRepositoryInterface $repository) {}

    public function execute(int $academicClassId, int $semesterId, int $validatedBy)
    {
        return $this->repository->updateStatus($academicClassId, $semesterId, [
            'status' => BulletinPublication::STATUS_VALIDATED,
            'validated_by' => $validatedBy,
            'validated_at' => now(),
        ]);
    }
}
