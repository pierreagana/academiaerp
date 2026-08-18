<?php

namespace App\Modules\Cards\Domain\Repositories;

interface CardTemplateRepositoryInterface
{
    public function findOrDefault(string $type);

    public function save(string $type, array $data);
}
