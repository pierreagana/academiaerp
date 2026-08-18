<?php

namespace App\Modules\Presence\Domain\Repositories;

interface AccessPointRepositoryInterface
{
    public function all();

    public function create(string $name);

    public function delete($id);

    public function ensureDefaults(): void;
}
