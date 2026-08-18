<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Application\Services\ModuleAdoptionService;
use App\Modules\SuperAdmin\Domain\Repositories\SaasModuleRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\SchoolRepositoryInterface;

class ListSaasModulesUseCase
{
    public function __construct(
        private SaasModuleRepositoryInterface $moduleRepository,
        private SchoolRepositoryInterface $schoolRepository,
        private ModuleAdoptionService $adoptionService
    ) {}

    public function execute(): array
    {
        $modulesData = $this->moduleRepository->getAll();
        $totalSchools = count($this->schoolRepository->getAll());

        $modules = [];
        foreach ($modulesData as $mod) {
            $adoptedSchools = $this->adoptionService->count($mod->slug);
            $usagePct = $totalSchools > 0 ? (int) round(($adoptedSchools / $totalSchools) * 100) : 0;

            $modules[] = (object)[
                'id'                   => $mod->id,
                'name'                 => $mod->name,
                'slug'                 => $mod->slug,
                'description'          => $mod->description,
                'status'               => $mod->status ?? 'active',
                'price'                => $mod->price,
                'icon'                 => $mod->icon ?? 'ph-puzzle-piece',
                'version'              => $mod->version,
                'active_schools_count' => $adoptedSchools,
                'revenue_mrr'          => $mod->price * $adoptedSchools,
                'usage_pct'            => $usagePct,
            ];
        }

        return $modules;
    }
}
