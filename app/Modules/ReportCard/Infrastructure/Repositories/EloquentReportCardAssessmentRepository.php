<?php

namespace App\Modules\ReportCard\Infrastructure\Repositories;

use App\Modules\ReportCard\Domain\Models\ReportCardAssessment;
use App\Modules\ReportCard\Domain\Repositories\ReportCardAssessmentRepositoryInterface;

class EloquentReportCardAssessmentRepository implements ReportCardAssessmentRepositoryInterface
{
    public function upsert(int $studentId, int $competencyId, int $semesterId, array $data)
    {
        return ReportCardAssessment::updateOrCreate(
            ['student_id' => $studentId, 'competency_id' => $competencyId, 'semester_id' => $semesterId],
            $data
        );
    }

    public function forStudentAndSemester(int $studentId, int $semesterId)
    {
        return ReportCardAssessment::where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->get()
            ->keyBy('competency_id');
    }

    public function forStudent(int $studentId)
    {
        return ReportCardAssessment::where('student_id', $studentId)
            ->with(['semester', 'competency.subdomain.domain'])
            ->get();
    }

    public function forSchoolAndSemester(int $schoolId, int $semesterId)
    {
        return ReportCardAssessment::where('semester_id', $semesterId)
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->with(['student.academicClass', 'competency.subdomain.domain'])
            ->get();
    }
}
