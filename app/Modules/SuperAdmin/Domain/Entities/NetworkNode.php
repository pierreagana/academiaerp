<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class NetworkNode
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $region = null,
        public readonly ?string $status = null,
        public readonly ?string $ipAddress = null,
        public readonly ?float $latencyMs = null,
        public readonly ?float $loadPct = null
    ) {}
}
