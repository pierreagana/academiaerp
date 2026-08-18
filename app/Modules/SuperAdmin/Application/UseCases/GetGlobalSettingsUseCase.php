<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\GlobalSettingRepositoryInterface;

class GetGlobalSettingsUseCase
{
    public function __construct(
        private GlobalSettingRepositoryInterface $settingRepository
    ) {}

    public function execute(): array
    {
        return $this->settingRepository->getAll();
    }
}
