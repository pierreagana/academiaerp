<?php

namespace App\Modules\Academic\Application\DTOs;

class CreateSyllabusDTO
{
    public array $data;
    public function __construct(array $data) { $this->data = $data; }
}
