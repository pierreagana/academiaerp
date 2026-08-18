<?php

namespace App\Modules\Bulletin\Application\UseCases;

use App\Modules\Bulletin\Domain\Repositories\BulletinEvaluationTypeRepositoryInterface;

class DeleteBulletinEvaluationTypeUseCase
{
    public function __construct(private BulletinEvaluationTypeRepositoryInterface $repository) {}

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
