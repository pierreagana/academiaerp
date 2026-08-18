<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface SystemLogRepositoryInterface
{
    public function getAll(): array;
    public function paginate(int $perPage = 10);
}
