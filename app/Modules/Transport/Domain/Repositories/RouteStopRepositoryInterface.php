<?php

namespace App\Modules\Transport\Domain\Repositories;

interface RouteStopRepositoryInterface
{
    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function find($id);

    public function forRoute($routeId);

    public function nextSequence($routeId): int;

    public function swapSequence($id, string $direction);

    public function syncStudents($stopId, array $studentIds);

    public function detachStudent($stopId, $studentId);
}
