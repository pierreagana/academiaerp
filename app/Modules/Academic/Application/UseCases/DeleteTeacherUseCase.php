<?php

namespace App\Modules\Academic\Application\UseCases;

use App\Models\User;
use App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface;

class DeleteTeacherUseCase
{
    private TeacherRepositoryInterface $repository;

    public function __construct(TeacherRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        // SoftDeletes on Teacher means the FK cascade never fires; revoke the portal login explicitly.
        User::where('teacher_id', $id)->delete();

        return $this->repository->delete($id);
    }
}
