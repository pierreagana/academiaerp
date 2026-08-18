<?php

namespace App\Modules\Homework\Application\UseCases;

use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use Illuminate\Support\Carbon;

class StartSessionUseCase
{
    public function execute(HomeworkAssignment $assignment): HomeworkAssignment
    {
        if (!$assignment->started_at) {
            $assignment->started_at = Carbon::now();
            $assignment->save();
        }

        return $assignment;
    }
}
