<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\UpdateFeeLevelDTO;
use App\Modules\Finance\Domain\Repositories\FeeLevelRepositoryInterface;

class UpdateFeeLevelUseCase
{
    private FeeLevelRepositoryInterface $repository;

    public function __construct(FeeLevelRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id, UpdateFeeLevelDTO $dto)
    {
        return $this->repository->update($id, $dto->data);
    }
}
