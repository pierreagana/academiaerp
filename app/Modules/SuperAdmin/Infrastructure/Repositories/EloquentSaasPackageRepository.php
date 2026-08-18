<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\SaasPackage as DomainSaasPackage;
use App\Modules\SuperAdmin\Domain\Models\SaasPackage as EloquentSaasPackage;
use App\Modules\SuperAdmin\Domain\Repositories\SaasPackageRepositoryInterface;

class EloquentSaasPackageRepository implements SaasPackageRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentSaasPackage::all()->map(function ($pkg) {
            return new DomainSaasPackage(
                id: $pkg->id,
                name: $pkg->name ?? 'Forfait Standard',
                slug: $pkg->slug ?? \Illuminate\Support\Str::slug($pkg->name ?? 'forfait'),
                description: $pkg->description,
                price: (float)($pkg->price ?? 0),
                billingCycle: $pkg->billing_cycle ?? 'mensuel',
                billing_cycle: $pkg->billing_cycle ?? 'mensuel',
                maxStudents: (int)($pkg->max_students ?? 0),
                maxStorageGb: (int)($pkg->max_storage_gb ?? 0),
                features: is_array($pkg->features) ? $pkg->features : json_decode($pkg->features ?? '[]', true),
                status: $pkg->status ?? 'active',
                is_popular: (bool)($pkg->is_popular ?? false)
            );
        })->toArray();
    }
}
