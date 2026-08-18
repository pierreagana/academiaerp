<?php

namespace App\Modules\Finance\Application\DTOs;

class CreateExpenseDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
