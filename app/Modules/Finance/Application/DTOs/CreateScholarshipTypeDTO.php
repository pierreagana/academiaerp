<?php

namespace App\Modules\Finance\Application\DTOs;

class CreateScholarshipTypeDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
