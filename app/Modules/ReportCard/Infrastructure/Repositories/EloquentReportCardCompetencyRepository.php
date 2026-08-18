<?php

namespace App\Modules\ReportCard\Infrastructure\Repositories;

use App\Modules\ReportCard\Domain\Models\ReportCardCompetency;
use App\Modules\ReportCard\Domain\Repositories\ReportCardCompetencyRepositoryInterface;

class EloquentReportCardCompetencyRepository implements ReportCardCompetencyRepositoryInterface
{
    public function create(array $data)
    {
        return ReportCardCompetency::create($data);
    }

    public function delete($id)
    {
        return $this->scoped()->find($id)?->delete();
    }

    public function forSchool(int $schoolId)
    {
        return ReportCardCompetency::whereHas('subdomain.domain', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->with('subdomain.domain')->get();
    }

    private function scoped()
    {
        return ReportCardCompetency::whereHas('subdomain.domain', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        });
    }
}
