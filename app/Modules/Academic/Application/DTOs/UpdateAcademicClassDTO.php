<?php

namespace App\Modules\Academic\Application\DTOs;

class UpdateAcademicClassDTO
{
    public array $data;
    public function __construct(array $data) { 
        $this->data = $data; 
    }
}
