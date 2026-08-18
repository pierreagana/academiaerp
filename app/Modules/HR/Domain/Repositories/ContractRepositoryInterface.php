<?php

namespace App\Modules\HR\Domain\Repositories;

interface ContractRepositoryInterface
{
    public function create(array $data);

    public function find($id);

    public function all();

    public function acknowledgeReminder($id): void;
}
