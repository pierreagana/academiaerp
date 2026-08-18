<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

use Carbon\Carbon;

class BackupLog
{
    public readonly ?Carbon $completedAtObj;

    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $fileName = null,
        public readonly float $sizeMb = 0.0,
        public readonly string $status = 'completed',
        public readonly string $type = 'full',
        public readonly ?string $completedAt = null
    ) {
        $this->completedAtObj = $completedAt ? Carbon::parse($completedAt) : Carbon::now();
    }

    public function __get(string $name)
    {
        return match ($name) {
            'file_name'    => $this->fileName ?? 'backup.sql',
            'size_mb'      => $this->sizeMb,
            'completed_at' => $this->completedAtObj,
            default        => property_exists($this, $name) ? $this->$name : null,
        };
    }
}
