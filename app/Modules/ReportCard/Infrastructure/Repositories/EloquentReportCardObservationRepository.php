<?php

namespace App\Modules\ReportCard\Infrastructure\Repositories;

use App\Modules\ReportCard\Domain\Models\ReportCardObservation;
use App\Modules\ReportCard\Domain\Repositories\ReportCardObservationRepositoryInterface;

class EloquentReportCardObservationRepository implements ReportCardObservationRepositoryInterface
{
    public function create(array $data)
    {
        return ReportCardObservation::create($data);
    }

    public function forStudent(int $studentId)
    {
        return ReportCardObservation::where('student_id', $studentId)
            ->with('teacher')
            ->latest()
            ->get();
    }
}
