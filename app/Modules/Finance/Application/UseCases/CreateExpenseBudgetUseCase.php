<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\CreateExpenseBudgetDTO;
use App\Modules\Finance\Domain\Repositories\ExpenseBudgetRepositoryInterface;

class CreateExpenseBudgetUseCase
{
    private ExpenseBudgetRepositoryInterface $repository;

    public function __construct(ExpenseBudgetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateExpenseBudgetDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
