<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface ServiceCatalogRepositoryInterface
{
    public function getAll(): array;
}
