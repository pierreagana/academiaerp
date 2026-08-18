<?php

namespace App\Modules\Finance\Domain\Repositories;

interface FeeLevelRepositoryInterface
{
    public function all(string $type = 'tuition');
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function findForLevel(string $level, string $academicYear);
}
