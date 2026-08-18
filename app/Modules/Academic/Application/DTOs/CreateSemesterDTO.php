<?php

namespace App\Modules\Academic\Application\DTOs;

class CreateSemesterDTO
{
    public array $data;
    public function __construct(array $data) { $this->data = $data; }
}
