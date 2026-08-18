<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\GlobalSetting as DomainGlobalSetting;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting as EloquentGlobalSetting;
use App\Modules\SuperAdmin\Domain\Repositories\GlobalSettingRepositoryInterface;

class EloquentGlobalSettingRepository implements GlobalSettingRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentGlobalSetting::all()->map(fn($s) => $this->mapToDomain($s))->toArray();
    }

    public function findByKey(string $key)
    {
        $setting = EloquentGlobalSetting::where('key', $key)->first();
        return $setting ? $this->mapToDomain($setting) : null;
    }

    private function mapToDomain(EloquentGlobalSetting $setting): DomainGlobalSetting
    {
        return new DomainGlobalSetting(
            id: $setting->id,
            key: $setting->key,
            value: $setting->value,
            group: $setting->group ?? 'general',
            type: $setting->type ?? 'string',
            description: $setting->description,
            isPublic: (bool)($setting->is_public ?? false)
        );
    }
}
