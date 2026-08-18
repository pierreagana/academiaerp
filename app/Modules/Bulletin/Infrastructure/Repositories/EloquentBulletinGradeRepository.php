<?php

namespace App\Modules\Bulletin\Infrastructure\Repositories;

use App\Modules\Bulletin\Domain\Models\BulletinGrade;
use App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface;

class EloquentBulletinGradeRepository implements BulletinGradeRepositoryInterface
{
    public function create(array $data)
    {
        return BulletinGrade::create($data);
    }

    public function delete(int $id)
    {
        return BulletinGrade::destroy($id);
    }

    public function find(int $id)
    {
        return BulletinGrade::find($id);
    }

    public function forClassAndSemester(int $academicClassId, int $semesterId)
    {
        return BulletinGrade::where('semester_id', $semesterId)
            ->whereHas('student', fn ($q) => $q->where('academic_class_id', $academicClassId))
            ->with(['subject', 'student', 'teacher', 'evaluationType'])
            ->get();
    }

    public function forStudent(int $studentId)
    {
        return BulletinGrade::where('student_id', $studentId)
            ->with(['subject', 'semester', 'teacher', 'evaluationType'])
            ->get();
    }

    public function forSchoolAndSemester(int $schoolId, int $semesterId)
    {
        return BulletinGrade::where('semester_id', $semesterId)
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->with(['subject', 'student.academicClass', 'evaluationType'])
            ->get();
    }
}
