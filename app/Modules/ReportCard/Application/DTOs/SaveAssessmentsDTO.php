<?php

namespace App\Modules\ReportCard\Application\DTOs;

class SaveAssessmentsDTO
{
    public int $competencyId;
    public int $semesterId;
    public ?int $subjectId;
    public ?int $assessedBy;
    /** @var array<int,string> student_id => level */
    public array $levels;

    public function __construct(int $competencyId, int $semesterId, ?int $subjectId, ?int $assessedBy, array $levels)
    {
        $this->competencyId = $competencyId;
        $this->semesterId = $semesterId;
        $this->subjectId = $subjectId;
        $this->assessedBy = $assessedBy;
        $this->levels = $levels;
    }
}
