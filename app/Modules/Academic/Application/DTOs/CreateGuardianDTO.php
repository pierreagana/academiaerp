<?php

namespace App\Modules\Academic\Application\DTOs;

class CreateGuardianDTO
{
    public array $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
