<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

use Carbon\Carbon;

class SystemLog
{
    public readonly ?Carbon $createdAtObj;

    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $level = 'info',
        public readonly ?string $message = '',
        public readonly ?string $source = 'System',
        public readonly ?string $createdAt = null,
        public readonly ?string $ipAddress = null
    ) {
        $this->createdAtObj = $createdAt ? Carbon::parse($createdAt) : Carbon::now();
    }

    public function __get(string $name)
    {
        return match ($name) {
            'created_at' => $this->createdAtObj,
            'ip_address' => $this->ipAddress ?? '127.0.0.1',
            default      => property_exists($this, $name) ? $this->$name : null,
        };
    }
}
