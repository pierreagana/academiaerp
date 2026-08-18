<?php

namespace App\Modules\Canteen\Application\DTOs;

class CreateMenuItemDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
