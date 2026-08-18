<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\ServiceCatalogRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\SaasModuleRepositoryInterface;

class ListServiceCatalogUseCase
{
    public function __construct(
        private ServiceCatalogRepositoryInterface $catalogRepository,
        private SaasModuleRepositoryInterface $saasModuleRepository
    ) {}

    public function execute(): array
    {
        $services = $this->catalogRepository->getAll();
        $packages = $this->saasModuleRepository->getAll(); // In this context packages/modules might overlap, we'll return both

        return [
            'services' => $services,
            'packages' => $packages
        ];
    }
}
