<?php

namespace App\Modules\Transport\Application\UseCases;

use App\Modules\Transport\Application\DTOs\CreateBusDTO;
use App\Modules\Transport\Domain\Repositories\BusRepositoryInterface;

class UpdateBusUseCase
{
    private BusRepositoryInterface $repository;

    public function __construct(BusRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id, CreateBusDTO $dto)
    {
        return $this->repository->update($id, $dto->data);
    }
}
