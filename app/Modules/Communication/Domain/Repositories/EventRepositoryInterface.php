<?php

namespace App\Modules\Communication\Domain\Repositories;

interface EventRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function upcoming($limit = 5);

    public function forMonth(int $year, int $month, array $filters = []);

    public function syncClasses($eventId, array $classIds);

    public function syncTeachers($eventId, array $teacherIds);
}
