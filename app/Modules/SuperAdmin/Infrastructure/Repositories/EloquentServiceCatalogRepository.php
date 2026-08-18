<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\ServiceCatalogItem as DomainServiceCatalogItem;
use App\Modules\SuperAdmin\Domain\Models\ServiceCatalogItem as EloquentServiceCatalogItem;
use App\Modules\SuperAdmin\Domain\Repositories\ServiceCatalogRepositoryInterface;

class EloquentServiceCatalogRepository implements ServiceCatalogRepositoryInterface
{
    public function getAll(): array
    {
        return EloquentServiceCatalogItem::all()->map(function ($item) {
            return new DomainServiceCatalogItem(
                id: $item->id,
                name: $item->name ?? 'Service',
                type: $item->type ?? 'Module',
                description: $item->description ?? '',
                priceTag: $item->price_tag ?? 'Inclus',
                priceColor: $item->price_color ?? 'emerald',
                icon: $item->icon ?? 'cog',
                iconBg: $item->icon_bg ?? 'bg-emerald-500/10',
                isEnabled: (bool)($item->is_enabled ?? true),
                isBeta: (bool)($item->is_beta ?? false)
            );
        })->toArray();
    }
}
