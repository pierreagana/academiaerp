<?php

namespace App\Modules\Academic\Application\DTOs;

class CreateTimetableBreakDTO
{
    public array $data;
    public function __construct(array $data) { $this->data = $data; }
}
