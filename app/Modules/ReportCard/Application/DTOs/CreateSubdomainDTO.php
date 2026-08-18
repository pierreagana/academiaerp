<?php

namespace App\Modules\ReportCard\Application\DTOs;

class CreateSubdomainDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
