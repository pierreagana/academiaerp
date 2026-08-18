<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\School as DomainSchool;
use App\Modules\SuperAdmin\Domain\Models\School as EloquentSchool;
use App\Modules\SuperAdmin\Domain\Repositories\SchoolRepositoryInterface;

class EloquentSchoolRepository implements SchoolRepositoryInterface
{
    public function getAll(): array
    {
        $eloquentSchools = EloquentSchool::orderBy('id', 'desc')->get();
        
        return $eloquentSchools->map(fn($s) => $this->mapToDomain($s))->toArray();
    }

    public function paginate(
        int $perPage = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $country = null,
        ?string $plan = null
    ) {
        $query = EloquentSchool::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('location', 'LIKE', '%' . $search . '%')
                  ->orWhere('contact_email', 'LIKE', '%' . $search . '%');
            });
        }

        if ($status && strtolower($status) !== 'tous les statuts' && strtolower($status) !== 'all') {
            $query->where('status', 'LIKE', '%' . strtolower($status) . '%');
        }

        if ($country && strtolower($country) !== 'tous les pays' && strtolower($country) !== 'all') {
            $query->where('location', 'LIKE', '%' . $country . '%');
        }

        if ($plan && strtolower($plan) !== 'tous les forfaits' && strtolower($plan) !== 'all') {
            $query->where('plan_name', 'LIKE', '%' . $plan . '%');
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
        $paginator->getCollection()->transform(fn($s) => $this->mapToDomain($s));
        
        return $paginator;
    }

    private function mapToDomain(EloquentSchool $school): DomainSchool
    {
        return new DomainSchool(
            id: $school->id,
            name: $school->name,
            code: '#EDU-' . str_pad($school->id, 4, '0', STR_PAD_LEFT),
            logo: $school->logo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(substr($school->name, 0, 2)) . '&background=0F3294&color=fff',
            location: $school->location ?? 'Dakar, Sénégal',
            region: $school->location ?? 'Dakar, Sénégal',
            status: strtolower($school->status ?? 'actif'),
            studentsCount: (int)($school->students_count ?? 0),
            package: $school->plan_name ?? 'Pro',
            type: $school->type ?? 'Secondaire (Lycée)',
            contactEmail: $school->contact_email ?? ('contact@' . strtolower(str_replace(' ', '', $school->name)) . '.sn'),
            contactPhone: $school->contact_phone ?? '+221 33 800 00 00',
            domain: $school->domain ?? (strtolower(str_replace(' ', '', $school->name)) . '.agana.school'),
            renewalDate: $school->created_at ? $school->created_at->addYear()->format('Y-m-d') : '-'
        );
    }
}
