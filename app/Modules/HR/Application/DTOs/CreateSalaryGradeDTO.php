<?php

namespace App\Modules\HR\Application\DTOs;

class CreateSalaryGradeDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
