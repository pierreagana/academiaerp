<?php

namespace App\Modules\Canteen\Domain\Repositories;

interface AccountRepositoryInterface
{
    public function syncRoster(): void;

    public function paginate($perPage = 15, array $filters = []);

    public function find($id);

    public function credit($id, float $amount);

    public function debit($id, float $amount);

    public function mealsCountThisMonth($accountId): int;
}
