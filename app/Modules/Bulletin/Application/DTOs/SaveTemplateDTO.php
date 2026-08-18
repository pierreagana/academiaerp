<?php

namespace App\Modules\Bulletin\Application\DTOs;

class SaveTemplateDTO
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
