<?php

namespace App\Modules\Canteen\Domain\Repositories;

interface ProductRepositoryInterface
{
    public function all();

    public function paginate($perPage = 10);

    public function find($id);

    public function create(array $data);

    public function criticalOrLow();

    public function recentMovements($limit = 5);
}
