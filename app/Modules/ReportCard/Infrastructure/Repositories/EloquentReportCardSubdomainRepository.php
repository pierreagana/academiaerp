<?php

namespace App\Modules\ReportCard\Infrastructure\Repositories;

use App\Modules\ReportCard\Domain\Models\ReportCardSubdomain;
use App\Modules\ReportCard\Domain\Repositories\ReportCardSubdomainRepositoryInterface;

class EloquentReportCardSubdomainRepository implements ReportCardSubdomainRepositoryInterface
{
    public function create(array $data)
    {
        return ReportCardSubdomain::create($data);
    }

    public function delete($id)
    {
        return ReportCardSubdomain::whereHas('domain', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->find($id)?->delete();
    }
}
