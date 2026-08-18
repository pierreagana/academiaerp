<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    App\Modules\SuperAdmin\Providers\SuperAdminServiceProvider::class,
    App\Modules\SchoolDashboard\Providers\SchoolDashboardServiceProvider::class,
    App\Modules\Academic\Infrastructure\Providers\AcademicServiceProvider::class,
    App\Modules\Finance\Infrastructure\Providers\FinanceServiceProvider::class,
    App\Modules\Communication\Infrastructure\Providers\CommunicationServiceProvider::class,
    App\Modules\Library\Infrastructure\Providers\LibraryServiceProvider::class,
    App\Modules\Canteen\Infrastructure\Providers\CanteenServiceProvider::class,
    App\Modules\Infirmary\Infrastructure\Providers\InfirmaryServiceProvider::class,
    App\Modules\Transport\Infrastructure\Providers\TransportServiceProvider::class,
    App\Modules\HR\Infrastructure\Providers\HRServiceProvider::class,
    App\Modules\Cards\Infrastructure\Providers\CardsServiceProvider::class,
    App\Modules\Presence\Infrastructure\Providers\PresenceServiceProvider::class,
    App\Modules\ReportCard\Infrastructure\Providers\ReportCardServiceProvider::class,
    App\Modules\Bulletin\Infrastructure\Providers\BulletinServiceProvider::class,
    App\Modules\Homework\Infrastructure\Providers\HomeworkServiceProvider::class,
    App\Modules\ParentPortal\Providers\ParentPortalServiceProvider::class,
];
