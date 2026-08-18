<?php

namespace App\Modules\Presence\Infrastructure\Repositories;

use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\Presence\Domain\Repositories\AttendanceRecordRepositoryInterface;

class EloquentAttendanceRecordRepository implements AttendanceRecordRepositoryInterface
{
    public function forClassAndDate(int $academicClassId, string $date)
    {
        return AttendanceRecord::where('school_id', auth()->user()->school_id)
            ->where('academic_class_id', $academicClassId)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');
    }

    public function upsert(int $studentId, array $data)
    {
        $schoolId = auth()->user()->school_id;

        return AttendanceRecord::updateOrCreate(
            ['school_id' => $schoolId, 'student_id' => $studentId, 'date' => $data['date']],
            $data
        );
    }
}
