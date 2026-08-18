<?php

namespace App\Modules\Canteen\Domain\Repositories;

interface MealRecordRepositoryInterface
{
    public function create(array $data);

    public function countToday(): int;

    public function averagePriceToday(): float;

    public function dailyCountsForWeek(string $weekStartDate): array;
}
