<?php

namespace App\Modules\Canteen\Application\UseCases;

use App\Modules\Canteen\Application\DTOs\CreateProductDTO;
use App\Modules\Canteen\Domain\Repositories\ProductRepositoryInterface;

class CreateProductUseCase
{
    private ProductRepositoryInterface $repository;

    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateProductDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
