<?php

namespace App\Modules\Homework\Application\DTOs;

class CreateHomeworkAssignmentDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
