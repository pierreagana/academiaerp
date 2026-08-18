<?php

namespace App\Modules\Canteen\Application\UseCases;

use App\Modules\Canteen\Domain\Repositories\AccountRepositoryInterface;

class SyncRosterUseCase
{
    private AccountRepositoryInterface $repository;

    public function __construct(AccountRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): void
    {
        $this->repository->syncRoster();
    }
}
