<?php

namespace App\Modules\Academic\Domain\Repositories;

interface TeacherRepositoryInterface
{
    public function all();
    public function paginate($perPage = 10, array $filters = []);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function syncClasses($teacherId, array $classIds);
}
