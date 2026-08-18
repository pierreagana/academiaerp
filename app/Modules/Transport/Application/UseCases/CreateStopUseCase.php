<?php

namespace App\Modules\Transport\Application\UseCases;

use App\Modules\Transport\Application\DTOs\CreateStopDTO;
use App\Modules\Transport\Domain\Repositories\RouteStopRepositoryInterface;

class CreateStopUseCase
{
    private RouteStopRepositoryInterface $repository;

    public function __construct(RouteStopRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateStopDTO $dto)
    {
        $data = $dto->data;
        $data['sequence'] = $this->repository->nextSequence($data['route_id']);

        return $this->repository->create($data);
    }
}
