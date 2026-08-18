<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

use Carbon\Carbon;

class BroadcastMessage
{
    public readonly ?Carbon $expiresAtObj;
    public readonly Carbon $createdAtObj;

    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $title = null,
        public readonly ?string $message = null,
        public readonly ?string $type = null,
        public readonly ?array $targetRoles = null,
        public readonly bool $isActive = true,
        public readonly ?string $expiresAt = null,
        public readonly ?string $createdAt = null
    ) {
        $this->expiresAtObj = $expiresAt ? Carbon::parse($expiresAt) : null;
        $this->createdAtObj = $createdAt ? Carbon::parse($createdAt) : Carbon::now();
    }

    public function __get(string $name)
    {
        return match ($name) {
            'is_active'    => $this->isActive,
            'expires_at'   => $this->expiresAtObj,
            'created_at'   => $this->createdAtObj,
            'target_roles' => $this->targetRoles ?? ['tous'],
            default        => property_exists($this, $name) ? $this->$name : null,
        };
    }
}
