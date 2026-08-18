<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\CreateFeeLevelDTO;
use App\Modules\Finance\Domain\Repositories\FeeLevelRepositoryInterface;

class CreateFeeLevelUseCase
{
    private FeeLevelRepositoryInterface $repository;

    public function __construct(FeeLevelRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateFeeLevelDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
