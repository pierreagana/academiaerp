<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\SaasModule as DomainSaasModule;
use App\Modules\SuperAdmin\Domain\Models\SaasModule as EloquentSaasModule;
use App\Modules\SuperAdmin\Domain\Repositories\SaasModuleRepositoryInterface;

class EloquentSaasModuleRepository implements SaasModuleRepositoryInterface
{
    public function getAll(): array
    {
        $eloquentModules = EloquentSaasModule::all();
        
        return $eloquentModules->map(function ($module) {
            return new DomainSaasModule(
                id: $module->id,
                name: $module->name,
                slug: $module->slug,
                description: $module->description ?? '',
                status: $module->status,
                price: (float)($module->price ?? 0),
                icon: $module->icon,
                version: $module->version ?? '1.0.0'
            );
        })->toArray();
    }
    
    public function paginate(int $perPage = 10)
    {
        $paginator = EloquentSaasModule::paginate($perPage);
        
        $paginator->getCollection()->transform(function ($module) {
            return new DomainSaasModule(
                id: $module->id,
                name: $module->name,
                slug: $module->slug,
                description: $module->description ?? '',
                status: $module->status,
                price: (float)($module->price ?? 0),
                icon: $module->icon,
                version: $module->version ?? '1.0.0'
            );
        });
        
        return $paginator;
    }
}
