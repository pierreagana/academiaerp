<?php

namespace App\Modules\Transport\Domain\Repositories;

interface RouteRepositoryInterface
{
    public function create(array $data);

    public function update($id, array $data);

    public function find($id);

    public function all();

    public function active();
}
