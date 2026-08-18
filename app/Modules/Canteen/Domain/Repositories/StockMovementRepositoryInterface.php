<?php

namespace App\Modules\Canteen\Domain\Repositories;

interface StockMovementRepositoryInterface
{
    public function create(array $data);

    public function wasteRatioForMonth(): float;
}
