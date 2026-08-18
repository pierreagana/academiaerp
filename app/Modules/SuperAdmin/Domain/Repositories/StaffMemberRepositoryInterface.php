<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface StaffMemberRepositoryInterface
{
    public function getAll(): array;
    public function paginate(int $perPage = 10);
}
