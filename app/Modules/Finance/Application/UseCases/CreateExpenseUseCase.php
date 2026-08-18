<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\CreateExpenseDTO;
use App\Modules\Finance\Domain\Repositories\ExpenseRepositoryInterface;

class CreateExpenseUseCase
{
    private ExpenseRepositoryInterface $repository;

    public function __construct(ExpenseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateExpenseDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
