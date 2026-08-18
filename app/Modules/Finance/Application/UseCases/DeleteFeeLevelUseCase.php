<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Domain\Repositories\FeeLevelRepositoryInterface;

class DeleteFeeLevelUseCase
{
    private FeeLevelRepositoryInterface $repository;

    public function __construct(FeeLevelRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
