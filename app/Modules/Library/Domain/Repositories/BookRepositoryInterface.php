<?php

namespace App\Modules\Library\Domain\Repositories;

interface BookRepositoryInterface
{
    public function all();

    public function paginate($perPage = 10, array $filters = []);

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function recent($limit = 5);

    public function categoryCounts();
}
