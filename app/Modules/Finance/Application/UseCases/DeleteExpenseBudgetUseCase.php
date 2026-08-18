<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Domain\Repositories\ExpenseBudgetRepositoryInterface;

class DeleteExpenseBudgetUseCase
{
    private ExpenseBudgetRepositoryInterface $repository;

    public function __construct(ExpenseBudgetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
