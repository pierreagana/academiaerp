<?php

namespace App\Modules\Infirmary\Domain\Repositories;

interface InterventionRepositoryInterface
{
    public function create(array $data);

    public function find($id);

    public function countToday(): int;

    public function activeToday(): int;

    public function motiveCountsToday(): array;

    public function motiveCountForRange(string $motive, string $start, string $end): int;

    public function recent(int $limit = 10);

    public function paginate(int $perPage = 15);

    public function forStudent($studentId);
}
