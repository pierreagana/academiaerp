<?php

namespace App\Modules\Canteen\Application\DTOs;

class CreateProductDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
