<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Domain\Repositories\ExpenseRepositoryInterface;

class DeleteExpenseUseCase
{
    private ExpenseRepositoryInterface $repository;

    public function __construct(ExpenseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
