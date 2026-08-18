<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\StaffMember as DomainStaffMember;
use App\Modules\SuperAdmin\Domain\Models\StaffMember as EloquentStaffMember;
use App\Modules\SuperAdmin\Domain\Repositories\StaffMemberRepositoryInterface;

class EloquentStaffMemberRepository implements StaffMemberRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentStaffMember::all()->map(fn($m) => $this->mapToDomain($m))->toArray();
    }
    
    public function paginate(int $perPage = 10)
    {
        $paginator = EloquentStaffMember::paginate($perPage);
        $paginator->getCollection()->transform(fn($m) => $this->mapToDomain($m));
        return $paginator;
    }
    
    private function mapToDomain(EloquentStaffMember $model): DomainStaffMember
    {
        return new DomainStaffMember(
            id: $model->id,
            staffCode: $model->staff_code ?? 'STF-000',
            name: $model->name ?? 'Membre du personnel',
            email: $model->email ?? 'staff@academia.com',
            role: $model->role ?? 'Agent',
            department: $model->department ?? 'Général',
            status: $model->status ?? 'active',
            lastLogin: $model->last_login
        );
    }
}
