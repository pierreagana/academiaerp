<?php

namespace App\Modules\HR\Domain\Repositories;

interface ContractTypeRepositoryInterface
{
    public function create(string $name);

    public function all();

    public function delete($id): void;

    public function ensureDefaults(): void;
}
