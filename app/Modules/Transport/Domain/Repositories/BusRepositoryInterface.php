<?php

namespace App\Modules\Transport\Domain\Repositories;

interface BusRepositoryInterface
{
    public function create(array $data);

    public function update($id, array $data);

    public function find($id);

    public function all();

    public function activeDrivable();

    public function countByStatus(): array;
}
