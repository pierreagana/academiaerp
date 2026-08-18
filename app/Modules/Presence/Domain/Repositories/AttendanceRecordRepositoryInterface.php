<?php

namespace App\Modules\Presence\Domain\Repositories;

interface AttendanceRecordRepositoryInterface
{
    public function forClassAndDate(int $academicClassId, string $date);

    public function upsert(int $studentId, array $data);
}
