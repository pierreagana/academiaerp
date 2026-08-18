<?php

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Application\DTOs\CreateBookDTO;
use App\Modules\Library\Domain\Repositories\BookRepositoryInterface;

class CreateBookUseCase
{
    private BookRepositoryInterface $repository;

    public function __construct(BookRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateBookDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
