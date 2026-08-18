<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface SaasPackageRepositoryInterface
{
    public function getAll(): array;
}
