<?php

namespace App\Modules\Infirmary\Domain\Repositories;

interface MedicationMovementRepositoryInterface
{
    public function create(array $data);

    public function dailyUsageRate($medicationId, int $days = 14): float;
}
