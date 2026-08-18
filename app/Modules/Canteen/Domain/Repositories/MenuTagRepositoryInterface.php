<?php

namespace App\Modules\Canteen\Domain\Repositories;

interface MenuTagRepositoryInterface
{
    public function all();

    public function create(string $name);

    public function delete($id);

    public function ensureDefaults(): void;
}
