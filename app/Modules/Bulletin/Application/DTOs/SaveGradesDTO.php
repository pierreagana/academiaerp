<?php

namespace App\Modules\Bulletin\Application\DTOs;

class SaveGradesDTO
{
    public int $subjectId;
    public int $semesterId;
    public ?int $teacherId;
    /** @var array<int,array{score:float,remark:?string}> student_id => ['score'=>..,'remark'=>..] */
    public array $entries;

    public function __construct(int $subjectId, int $semesterId, ?int $teacherId, array $entries)
    {
        $this->subjectId = $subjectId;
        $this->semesterId = $semesterId;
        $this->teacherId = $teacherId;
        $this->entries = $entries;
    }
}
