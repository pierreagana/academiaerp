<?php

namespace App\Modules\Academic\Application\DTOs;

class CreateStudentDTO
{
    public array $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
