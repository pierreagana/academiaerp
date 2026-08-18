<?php

namespace App\Modules\Canteen\Application\DTOs;

class AdjustStockDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
