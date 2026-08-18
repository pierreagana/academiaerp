<?php

namespace App\Modules\Canteen\Application\UseCases;

use App\Modules\Canteen\Domain\Repositories\MenuRepositoryInterface;

class DeleteMenuItemUseCase
{
    private MenuRepositoryInterface $repository;

    public function __construct(MenuRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->deleteItem($id);
    }
}
