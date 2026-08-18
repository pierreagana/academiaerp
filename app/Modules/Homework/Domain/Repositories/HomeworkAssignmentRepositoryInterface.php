<?php

namespace App\Modules\Homework\Domain\Repositories;

interface HomeworkAssignmentRepositoryInterface
{
    public function create(array $data);

    public function find(int $id);
}
