<?php

namespace App\Modules\Communication\Application\DTOs;

class UpdateEventDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
