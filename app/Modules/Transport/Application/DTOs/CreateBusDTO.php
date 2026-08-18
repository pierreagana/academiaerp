<?php

namespace App\Modules\Transport\Application\DTOs;

class CreateBusDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
