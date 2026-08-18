<?php

namespace App\Modules\Academic\Application\DTOs;

class UpdateLanguageDTO
{
    public array $data;
    public function __construct(array $data) { 
        $this->data = $data; 
    }
}
