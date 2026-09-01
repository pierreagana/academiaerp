<?php

namespace App\Modules\Academic\Domain\Repositories;

interface TimetableBreakRepositoryInterface
{
    public function allForClass($classId);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
