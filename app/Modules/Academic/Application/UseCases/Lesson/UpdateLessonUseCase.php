<?php

namespace App\Modules\Academic\Application\UseCases\Lesson;

use App\Modules\Academic\Domain\Repositories\LessonRepositoryInterface;
use App\Modules\Academic\Application\DTOs\Lesson\UpdateLessonDTO;

class UpdateLessonUseCase
{
    private $repository;

    public function __construct(LessonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id, UpdateLessonDTO $dto): bool
    {
        return $this->repository->update($id, $dto->toArray());
    }
}
