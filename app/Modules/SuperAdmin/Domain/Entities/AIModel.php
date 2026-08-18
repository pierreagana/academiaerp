<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class AIModel
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $provider = null,
        public readonly ?string $status = null,
        public readonly ?string $statusLabel = null,
        public readonly ?string $latency = null,
        public readonly ?string $color = null
    ) {}
}
