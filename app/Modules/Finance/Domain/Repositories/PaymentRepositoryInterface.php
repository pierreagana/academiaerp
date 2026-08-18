<?php

namespace App\Modules\Finance\Domain\Repositories;

interface PaymentRepositoryInterface
{
    public function create(array $data);
    public function forStudent($studentId, ?string $type = null);
    public function monthlyTotals(int $schoolId, int $months = 7, string $type = 'tuition');
}
