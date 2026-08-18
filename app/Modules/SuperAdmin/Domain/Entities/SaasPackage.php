<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class SaasPackage
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $slug = null,
        public readonly ?string $description = null,
        public readonly float $price = 0.0,
        public readonly ?string $billingCycle = 'mensuel',
        public readonly ?string $billing_cycle = 'mensuel',
        public readonly int $maxStudents = 0,
        public readonly int $maxStorageGb = 0,
        public readonly array $features = [],
        public readonly ?string $status = 'active',
        public readonly bool $is_popular = false
    ) {}
}
