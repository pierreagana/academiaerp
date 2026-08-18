<?php

namespace App\Modules\Academic\Application\DTOs;

class UpdateSemesterDTO
{
    public array $data;
    public function __construct(array $data) { 
        $this->data = $data; 
    }
}
