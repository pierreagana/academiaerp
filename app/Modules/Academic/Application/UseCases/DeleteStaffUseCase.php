<?php

namespace App\Modules\Academic\Application\UseCases;

use App\Models\User;
use App\Modules\Academic\Domain\Repositories\StaffRepositoryInterface;

class DeleteStaffUseCase
{
    private StaffRepositoryInterface $repository;

    public function __construct(StaffRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        // SoftDeletes on Staff means the FK cascade never fires; revoke the portal login explicitly.
        User::where('staff_id', $id)->delete();

        return $this->repository->delete($id);
    }
}
