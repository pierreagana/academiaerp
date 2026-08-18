<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\StaffMemberRepositoryInterface;

class ListStaffMembersUseCase
{
    public function __construct(
        private StaffMemberRepositoryInterface $staffRepository
    ) {}

    public function execute(int $perPage = 10)
    {
        return $this->staffRepository->paginate($perPage);
    }
}
