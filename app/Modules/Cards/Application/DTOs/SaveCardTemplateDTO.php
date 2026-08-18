<?php

namespace App\Modules\Cards\Application\DTOs;

class SaveCardTemplateDTO
{
    public string $cardType;
    public array $data;

    public function __construct(string $cardType, array $data)
    {
        $this->cardType = $cardType;
        $this->data = $data;
    }
}
