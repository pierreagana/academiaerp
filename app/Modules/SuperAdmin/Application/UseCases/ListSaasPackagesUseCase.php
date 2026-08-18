<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\SaasPackageRepositoryInterface;

class ListSaasPackagesUseCase
{
    public function __construct(
        private SaasPackageRepositoryInterface $packageRepository
    ) {}

    public function execute(): array
    {
        return $this->packageRepository->getAll();
    }
}
