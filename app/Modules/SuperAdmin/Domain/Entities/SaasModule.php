<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class SaasModule
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $description,
        public readonly string $status,
        public readonly float $price,
        public readonly ?string $icon,
        public readonly string $version = '1.0.0'
    ) {}
}
