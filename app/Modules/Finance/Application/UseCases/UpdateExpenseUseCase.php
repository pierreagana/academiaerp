<?php

namespace App\Modules\Finance\Application\UseCases;

use App\Modules\Finance\Application\DTOs\UpdateExpenseDTO;
use App\Modules\Finance\Domain\Repositories\ExpenseRepositoryInterface;

class UpdateExpenseUseCase
{
    private ExpenseRepositoryInterface $repository;

    public function __construct(ExpenseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id, UpdateExpenseDTO $dto)
    {
        return $this->repository->update($id, $dto->data);
    }
}
