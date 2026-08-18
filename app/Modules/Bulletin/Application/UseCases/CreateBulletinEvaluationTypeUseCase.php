<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Bulletin\Application\DTOs\CreateBulletinEvaluationTypeDTO;
use App\Modules\Bulletin\Domain\Repositories\BulletinEvaluationTypeRepositoryInterface;

class CreateBulletinEvaluationTypeUseCase
{
    public function __construct(private BulletinEvaluationTypeRepositoryInterface $repository) {}

    public function execute(CreateBulletinEvaluationTypeDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
