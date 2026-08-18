<?php

namespace App\Modules\Transport\Domain\Repositories;

interface DriverRepositoryInterface
{
    public function create(array $data);

    public function find($id);

    public function all();
}
