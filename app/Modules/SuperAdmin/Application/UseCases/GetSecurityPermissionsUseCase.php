<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\GlobalSettingRepositoryInterface;

class GetSecurityPermissionsUseCase
{
    public function __construct(
        private GlobalSettingRepositoryInterface $settingRepository
    ) {}

    public function execute(): array
    {
        $settings = collect($this->settingRepository->getAll())->mapWithKeys(fn($s) => [$s->key => $s->value]);

        return [
            '2fa_enabled' => (bool)$settings->get('security_2fa_enabled', true),
            'session_timeout' => (int)$settings->get('security_session_timeout', 120),
            'password_policy' => $settings->get('security_password_policy', 'strong'),
        ];
    }
}
