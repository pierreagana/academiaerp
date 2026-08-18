<?php

namespace App\Modules\Presence\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Presence\Domain\Repositories\AttendanceRecordRepositoryInterface;
use App\Modules\Presence\Infrastructure\Repositories\EloquentAttendanceRecordRepository;

use App\Modules\Presence\Domain\Repositories\AccessPointRepositoryInterface;
use App\Modules\Presence\Infrastructure\Repositories\EloquentAccessPointRepository;

use App\Modules\Presence\Domain\Repositories\AccessLogRepositoryInterface;
use App\Modules\Presence\Infrastructure\Repositories\EloquentAccessLogRepository;

class PresenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AttendanceRecordRepositoryInterface::class, EloquentAttendanceRecordRepository::class);
        $this->app->bind(AccessPointRepositoryInterface::class, EloquentAccessPointRepository::class);
        $this->app->bind(AccessLogRepositoryInterface::class, EloquentAccessLogRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
