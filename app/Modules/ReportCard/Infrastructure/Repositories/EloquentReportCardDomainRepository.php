<?php

namespace App\Modules\ReportCard\Infrastructure\Repositories;

use App\Modules\ReportCard\Domain\Models\ReportCardDomain;
use App\Modules\ReportCard\Domain\Repositories\ReportCardDomainRepositoryInterface;

class EloquentReportCardDomainRepository implements ReportCardDomainRepositoryInterface
{
    public function allWithTree()
    {
        return ReportCardDomain::where('school_id', auth()->user()->school_id)
            ->with('subdomains.competencies')
            ->orderBy('cycle')
            ->orderBy('name')
            ->get();
    }

    public function find($id)
    {
        return ReportCardDomain::where('school_id', auth()->user()->school_id)->find($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = auth()->user()->school_id;

        return ReportCardDomain::create($data);
    }

    public function delete($id)
    {
        return $this->find($id)?->delete();
    }
}
