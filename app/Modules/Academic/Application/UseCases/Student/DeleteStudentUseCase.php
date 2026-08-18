<?php

namespace App\Modules\Academic\Application\UseCases\Student;

use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;

class DeleteStudentUseCase
{
    private $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id)
    {
        return $this->repository->delete($id);
    }
}
