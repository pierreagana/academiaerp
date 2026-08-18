<?php

namespace App\Modules\Library\Infrastructure\Providers;

use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

use App\Modules\Library\Domain\Repositories\BookCategoryRepositoryInterface;
use App\Modules\Library\Infrastructure\Repositories\EloquentBookCategoryRepository;

use App\Modules\Library\Domain\Repositories\BookRepositoryInterface;
use App\Modules\Library\Infrastructure\Repositories\EloquentBookRepository;

use App\Modules\Library\Domain\Repositories\LoanRepositoryInterface;
use App\Modules\Library\Infrastructure\Repositories\EloquentLoanRepository;

use App\Modules\Library\Domain\Repositories\LibrarySettingRepositoryInterface;
use App\Modules\Library\Infrastructure\Repositories\EloquentLibrarySettingRepository;

class LibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BookCategoryRepositoryInterface::class, EloquentBookCategoryRepository::class);
        $this->app->bind(BookRepositoryInterface::class, EloquentBookRepository::class);
        $this->app->bind(LoanRepositoryInterface::class, EloquentLoanRepository::class);
        $this->app->bind(LibrarySettingRepositoryInterface::class, EloquentLibrarySettingRepository::class);
    }

    public function boot(): void
    {
        Relation::morphMap([
            'student' => Student::class,
            'teacher' => Teacher::class,
            'staff' => Staff::class,
        ]);
    }
}
