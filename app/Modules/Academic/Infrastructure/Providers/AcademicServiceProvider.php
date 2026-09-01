<?php

namespace App\Modules\Academic\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentSemesterRepository;

use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentAcademicClassRepository;

use App\Modules\Academic\Domain\Repositories\LessonRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentLessonRepository;

use App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentSubjectRepository;

use App\Modules\Academic\Domain\Repositories\SyllabusRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentSyllabusRepository;

use App\Modules\Academic\Domain\Repositories\TimetableRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentTimetableRepository;

use App\Modules\Academic\Domain\Repositories\LanguageRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentLanguageRepository;

use App\Modules\Academic\Domain\Repositories\GuardianRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentGuardianRepository;

use App\Modules\Academic\Domain\Repositories\StudentRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentStudentRepository;

use App\Modules\Academic\Domain\Repositories\TeacherRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentTeacherRepository;

use App\Modules\Academic\Domain\Repositories\StaffRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentStaffRepository;

use App\Modules\Academic\Domain\Repositories\TimetableBreakRepositoryInterface;
use App\Modules\Academic\Infrastructure\Repositories\EloquentTimetableBreakRepository;

class AcademicServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SemesterRepositoryInterface::class, EloquentSemesterRepository::class);
        $this->app->bind(AcademicClassRepositoryInterface::class, EloquentAcademicClassRepository::class);
        $this->app->bind(LessonRepositoryInterface::class, EloquentLessonRepository::class);
        $this->app->bind(SubjectRepositoryInterface::class, EloquentSubjectRepository::class);
        $this->app->bind(SyllabusRepositoryInterface::class, EloquentSyllabusRepository::class);
        $this->app->bind(TimetableRepositoryInterface::class, EloquentTimetableRepository::class);
        $this->app->bind(LanguageRepositoryInterface::class, EloquentLanguageRepository::class);
        $this->app->bind(GuardianRepositoryInterface::class, EloquentGuardianRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, EloquentStudentRepository::class);
        $this->app->bind(TeacherRepositoryInterface::class, EloquentTeacherRepository::class);
        $this->app->bind(StaffRepositoryInterface::class, EloquentStaffRepository::class);
        $this->app->bind(\App\Modules\Academic\Domain\Repositories\BuildingRepositoryInterface::class, \App\Modules\Academic\Infrastructure\Repositories\BuildingRepository::class);
        $this->app->bind(\App\Modules\Academic\Domain\Repositories\RoomRepositoryInterface::class, \App\Modules\Academic\Infrastructure\Repositories\RoomRepository::class);
        $this->app->bind(\App\Modules\Academic\Domain\Repositories\StudentClassMovementRepositoryInterface::class, \App\Modules\Academic\Infrastructure\Repositories\EloquentStudentClassMovementRepository::class);
        $this->app->bind(\App\Modules\Academic\Domain\Repositories\BranchRepositoryInterface::class, \App\Modules\Academic\Infrastructure\Repositories\EloquentBranchRepository::class);
        $this->app->bind(TimetableBreakRepositoryInterface::class, EloquentTimetableBreakRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
