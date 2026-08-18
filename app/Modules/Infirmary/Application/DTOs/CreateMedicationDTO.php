<?php

namespace App\Modules\Infirmary\Application\DTOs;

class CreateMedicationDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
