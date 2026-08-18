<?php

namespace App\Modules\Academic\Application\UseCases\Student;

use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;
use App\Modules\Academic\Application\DTOs\UpdateStudentDTO;

class UpdateStudentUseCase
{
    private $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id, UpdateStudentDTO $dto)
    {
        return $this->repository->update($id, $dto->data);
    }
}
