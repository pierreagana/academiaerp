<?php

namespace App\Modules\Academic\Application\DTOs;

class UpdateSubjectDTO
{
    public array $data;
    public function __construct(array $data) { 
        $this->data = $data; 
    }
}
