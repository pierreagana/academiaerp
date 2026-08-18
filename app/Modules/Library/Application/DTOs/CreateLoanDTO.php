<?php

namespace App\Modules\Library\Application\DTOs;

class CreateLoanDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
