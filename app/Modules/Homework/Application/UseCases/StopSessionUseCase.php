<?php

namespace App\Modules\Homework\Application\UseCases;

use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use Illuminate\Support\Carbon;

class StopSessionUseCase
{
    public function execute(HomeworkAssignment $assignment): HomeworkAssignment
    {
        if (!$assignment->ended_at) {
            $assignment->ended_at = Carbon::now();
            $assignment->save();
        }

        return $assignment;
    }
}
