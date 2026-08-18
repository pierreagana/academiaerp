<?php

namespace App\Modules\Canteen\Application\UseCases;

use App\Modules\Canteen\Application\DTOs\CreateMenuItemDTO;
use App\Modules\Canteen\Domain\Repositories\MenuRepositoryInterface;

class SaveMenuItemUseCase
{
    private MenuRepositoryInterface $repository;

    public function __construct(MenuRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateMenuItemDTO $dto)
    {
        return $this->repository->saveItem($dto->data);
    }
}
