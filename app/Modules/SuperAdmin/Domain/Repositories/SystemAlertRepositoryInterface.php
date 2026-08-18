<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface SystemAlertRepositoryInterface
{
    public function getAll(): array;
}
