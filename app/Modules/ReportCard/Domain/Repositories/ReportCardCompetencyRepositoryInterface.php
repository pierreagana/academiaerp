<?php

namespace App\Modules\ReportCard\Domain\Repositories;

interface ReportCardCompetencyRepositoryInterface
{
    public function create(array $data);

    public function delete($id);

    /** All competencies for a school, keyed for the evaluation grid selector. */
    public function forSchool(int $schoolId);
}
