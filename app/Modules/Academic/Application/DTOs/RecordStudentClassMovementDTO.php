<?php

namespace App\Modules\Academic\Application\DTOs;

class RecordStudentClassMovementDTO
{
    public array $data;
    public function __construct(array $data) { $this->data = $data; }
}
