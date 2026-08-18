<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class ServiceCatalogItem
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $type = null,
        public readonly ?string $description = null,
        public readonly ?string $priceTag = null,
        public readonly ?string $priceColor = null,
        public readonly ?string $icon = null,
        public readonly ?string $iconBg = null,
        public readonly bool $isEnabled = true,
        public readonly bool $isBeta = false
    ) {}
}
