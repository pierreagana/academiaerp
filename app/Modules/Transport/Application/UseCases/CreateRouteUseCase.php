<?php

namespace App\Modules\Transport\Application\UseCases;

use App\Modules\Transport\Application\DTOs\CreateRouteDTO;
use App\Modules\Transport\Domain\Repositories\RouteRepositoryInterface;

class CreateRouteUseCase
{
    private RouteRepositoryInterface $repository;

    public function __construct(RouteRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateRouteDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
