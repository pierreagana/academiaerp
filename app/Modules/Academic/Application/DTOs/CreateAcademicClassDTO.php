<?php

namespace App\Modules\Academic\Application\DTOs;

class CreateAcademicClassDTO
{
    public array $data;
    public function __construct(array $data) { $this->data = $data; }
}
