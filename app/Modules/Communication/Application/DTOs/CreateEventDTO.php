<?php

namespace App\Modules\Communication\Application\DTOs;

class CreateEventDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
