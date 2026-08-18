<?php

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Domain\Repositories\LoanRepositoryInterface;

class RemindOverdueLoansUseCase
{
    private LoanRepositoryInterface $repository;

    public function __construct(LoanRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): int
    {
        $overdueLoans = $this->repository->overdue();
        $ids = $overdueLoans->pluck('id')->all();

        if (!empty($ids)) {
            $this->repository->markReminded($ids);
        }

        return count($ids);
    }
}
