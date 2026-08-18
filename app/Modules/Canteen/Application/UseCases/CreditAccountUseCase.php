<?php

namespace App\Modules\Canteen\Application\UseCases;

use App\Modules\Canteen\Domain\Repositories\AccountRepositoryInterface;

class CreditAccountUseCase
{
    private AccountRepositoryInterface $repository;

    public function __construct(AccountRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($accountId, float $amount)
    {
        return $this->repository->credit($accountId, $amount);
    }
}
