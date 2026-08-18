<?php

namespace App\Modules\Academic\Application\UseCases\Lesson;

use App\Modules\Academic\Domain\Repositories\LessonRepositoryInterface;

class DeleteLessonUseCase
{
    private $repository;

    public function __construct(LessonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute($id): bool
    {
        return $this->repository->delete($id);
    }
}
