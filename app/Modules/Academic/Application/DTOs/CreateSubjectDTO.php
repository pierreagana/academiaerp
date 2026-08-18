<?php

namespace App\Modules\Academic\Application\DTOs;

class CreateSubjectDTO
{
    public array $data;
    public function __construct(array $data) { $this->data = $data; }
}
