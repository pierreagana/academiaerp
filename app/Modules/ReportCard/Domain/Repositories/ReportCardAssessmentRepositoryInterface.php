<?php

namespace App\Modules\ReportCard\Domain\Repositories;

interface ReportCardAssessmentRepositoryInterface
{
    /** Upsert one student+competency+semester assessment. */
    public function upsert(int $studentId, int $competencyId, int $semesterId, array $data);

    /** Latest assessments for a student in a given semester, keyed by competency_id. */
    public function forStudentAndSemester(int $studentId, int $semesterId);

    /** All assessments for a student across semesters (for the T1 vs T2 radar). */
    public function forStudent(int $studentId);

    /** All assessments recorded this semester for a school (for dashboard aggregates). */
    public function forSchoolAndSemester(int $schoolId, int $semesterId);
}
