<?php

namespace App\Modules\ReportCard\Application\DTOs;

class CreateCompetencyDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
