<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class SystemAlertRule
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $title = null,
        public readonly ?string $severity = null,
        public readonly ?string $metric = null,
        public readonly ?string $threshold = null,
        public readonly bool $isActive = true
    ) {}
}
