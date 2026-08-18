<?php

namespace App\Modules\Academic\Application\DTOs;

class CreateTimetableDTO
{
    public array $data;
    public function __construct(array $data) { $this->data = $data; }
}
