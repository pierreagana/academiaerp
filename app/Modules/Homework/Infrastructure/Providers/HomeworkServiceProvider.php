<?php

namespace App\Modules\Homework\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Homework\Domain\Repositories\HomeworkAssignmentRepositoryInterface;
use App\Modules\Homework\Infrastructure\Repositories\EloquentHomeworkAssignmentRepository;

use App\Modules\Homework\Domain\Repositories\HomeworkSubmissionRepositoryInterface;
use App\Modules\Homework\Infrastructure\Repositories\EloquentHomeworkSubmissionRepository;

use App\Modules\Homework\Domain\Repositories\HomeworkAttendanceRepositoryInterface;
use App\Modules\Homework\Infrastructure\Repositories\EloquentHomeworkAttendanceRepository;

class HomeworkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HomeworkAssignmentRepositoryInterface::class, EloquentHomeworkAssignmentRepository::class);
        $this->app->bind(HomeworkSubmissionRepositoryInterface::class, EloquentHomeworkSubmissionRepository::class);
        $this->app->bind(HomeworkAttendanceRepositoryInterface::class, EloquentHomeworkAttendanceRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
