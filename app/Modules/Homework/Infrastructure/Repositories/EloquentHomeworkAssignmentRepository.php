<?php

namespace App\Modules\Homework\Infrastructure\Repositories;

use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use App\Modules\Homework\Domain\Repositories\HomeworkAssignmentRepositoryInterface;

class EloquentHomeworkAssignmentRepository implements HomeworkAssignmentRepositoryInterface
{
    public function create(array $data)
    {
        return HomeworkAssignment::create($data);
    }

    public function find(int $id)
    {
        return HomeworkAssignment::find($id);
    }
}
