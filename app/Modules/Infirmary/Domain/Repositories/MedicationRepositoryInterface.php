<?php

namespace App\Modules\Infirmary\Domain\Repositories;

interface MedicationRepositoryInterface
{
    public function all();

    public function paginate(int $perPage = 10);

    public function find($id);

    public function create(array $data);

    public function expiringWithin(int $days);

    public function globalStockLevel(): float;

    public function recentMovements(int $limit = 5);
}
