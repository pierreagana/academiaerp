<?php

namespace App\Modules\Academic\Application\UseCases\Lesson;

use App\Modules\Academic\Domain\Repositories\LessonRepositoryInterface;
use App\Modules\Academic\Application\DTOs\Lesson\CreateLessonDTO;
use App\Modules\Academic\Domain\Models\Lesson;

class CreateLessonUseCase
{
    private $repository;

    public function __construct(LessonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateLessonDTO $dto): Lesson
    {
        return $this->repository->create($dto->toArray());
    }
}
