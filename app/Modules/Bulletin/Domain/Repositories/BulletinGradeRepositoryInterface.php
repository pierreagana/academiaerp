<?php

namespace App\Modules\Bulletin\Domain\Repositories;

interface BulletinGradeRepositoryInterface
{
    /** Creates one new grade entry. Several entries of the same evaluation type are valid (e.g. several interrogations). */
    public function create(array $data);

    public function delete(int $id);

    public function find(int $id);

    /** All grades for a class's students in a given semester. */
    public function forClassAndSemester(int $academicClassId, int $semesterId);

    /** All grades for a single student, across semesters (for stats/print). */
    public function forStudent(int $studentId);

    /** All grades recorded this semester for a school (for dashboard aggregates). */
    public function forSchoolAndSemester(int $schoolId, int $semesterId);
}
