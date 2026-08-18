<?php

namespace App\Modules\Library\Domain\Repositories;

interface LoanRepositoryInterface
{
    public function paginateActive($perPage = 10, array $filters = []);

    public function find($id);

    public function create(array $data);

    public function markReturned($id);

    public function findActiveByBookIdentifier(string $identifier);

    public function overdue($limit = null);

    public function distinctBorrowersCount(): int;

    public function monthlyCounts(int $months): array;

    public function markReminded(array $loanIds): void;
}
