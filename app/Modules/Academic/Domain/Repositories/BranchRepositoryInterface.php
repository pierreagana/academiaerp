<?php

namespace App\Modules\Academic\Domain\Repositories;

interface BranchRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function mainBranch();

    public function setMain($id);
}
