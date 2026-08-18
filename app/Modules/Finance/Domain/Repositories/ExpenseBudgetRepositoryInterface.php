<?php

namespace App\Modules\Finance\Domain\Repositories;

interface ExpenseBudgetRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
