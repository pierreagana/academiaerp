<?php

namespace App\Modules\Bulletin\Domain\Repositories;

interface BulletinEvaluationTypeRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
