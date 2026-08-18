<?php

namespace App\Modules\Homework\Application\UseCases;

use App\Modules\Homework\Application\DTOs\CreateHomeworkAssignmentDTO;
use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use App\Modules\Homework\Domain\Repositories\HomeworkAssignmentRepositoryInterface;

class CreateHomeworkAssignmentUseCase
{
    public function __construct(private HomeworkAssignmentRepositoryInterface $repository) {}

    public function execute(CreateHomeworkAssignmentDTO $dto): HomeworkAssignment
    {
        return $this->repository->create($dto->data);
    }
}
