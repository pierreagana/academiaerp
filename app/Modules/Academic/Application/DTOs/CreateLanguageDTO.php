<?php

namespace App\Modules\Academic\Application\DTOs;

class CreateLanguageDTO
{
    public array $data;
    public function __construct(array $data) { $this->data = $data; }
}
