<?php

namespace App\Modules\Communication\Application\UseCases;

use App\Modules\Communication\Domain\Repositories\EventRepositoryInterface;

class DeleteEventUseCase
{
    private EventRepositoryInterface $repository;

    public function __construct(EventRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
