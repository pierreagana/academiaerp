<?php

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Domain\Repositories\LoanRepositoryInterface;

class ReturnLoanUseCase
{
    private LoanRepositoryInterface $repository;

    public function __construct(LoanRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->markReturned($id);
    }
}
