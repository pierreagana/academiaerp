<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class GlobalSetting
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $key = null,
        public readonly ?string $value = null,
        public readonly ?string $group = 'general',
        public readonly ?string $type = 'string',
        public readonly ?string $description = null,
        public readonly bool $isPublic = false
    ) {}
}
