<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Bulletin\Application\DTOs\UpdateBulletinEvaluationTypeDTO;
use App\Modules\Bulletin\Domain\Repositories\BulletinEvaluationTypeRepositoryInterface;

class UpdateBulletinEvaluationTypeUseCase
{
    public function __construct(private BulletinEvaluationTypeRepositoryInterface $repository) {}

    public function execute($id, UpdateBulletinEvaluationTypeDTO $dto)
    {
        return $this->repository->update($id, $dto->data);
    }
}
