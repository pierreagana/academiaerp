<?php

namespace App\Modules\Library\Domain\Repositories;

interface BookCategoryRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function delete($id);
}
