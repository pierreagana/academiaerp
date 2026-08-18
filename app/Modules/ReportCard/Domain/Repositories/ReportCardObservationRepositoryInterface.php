<?php

namespace App\Modules\ReportCard\Domain\Repositories;

interface ReportCardObservationRepositoryInterface
{
    public function create(array $data);

    public function forStudent(int $studentId);
}
