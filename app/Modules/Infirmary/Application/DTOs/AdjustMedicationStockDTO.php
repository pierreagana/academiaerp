<?php

namespace App\Modules\Infirmary\Application\DTOs;

class AdjustMedicationStockDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
