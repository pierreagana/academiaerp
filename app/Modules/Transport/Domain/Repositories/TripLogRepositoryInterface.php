<?php

namespace App\Modules\Transport\Domain\Repositories;

interface TripLogRepositoryInterface
{
    public function create(array $data);

    public function paginate(int $perPage = 15, array $filters = []);

    public function forRange(string $start, string $end);

    public function latestForBusOnDate($busId, string $date);
}
