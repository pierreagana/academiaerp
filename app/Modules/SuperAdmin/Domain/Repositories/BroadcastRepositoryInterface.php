<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface BroadcastRepositoryInterface
{
    public function getAll(): array;
}
