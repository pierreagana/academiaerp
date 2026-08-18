<?php

namespace App\Modules\Academic\Application\UseCases\Student;

use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;
use App\Modules\Academic\Application\DTOs\CreateStudentDTO;

class CreateStudentUseCase
{
    private $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateStudentDTO $dto)
    {
        return $this->repository->create($dto->data);
    }
}
