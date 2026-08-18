<?php

namespace App\Modules\HR\Domain\Repositories;

interface PayrollComponentRepositoryInterface
{
    public function create(array $data);

    public function all();

    public function toggle($id): void;

    public function ensureDefaults(): void;
}
