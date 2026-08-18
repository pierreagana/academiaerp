<?php

namespace App\Modules\SuperAdmin\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;

use App\Modules\SuperAdmin\Domain\Repositories\DashboardRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\SchoolRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\RegistrationRequestRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\InvoiceRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\SaasModuleRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\GlobalSettingRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\StaffMemberRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\NetworkNodeRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\BackupRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\SystemLogRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\SystemAlertRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\AIModelRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\ServiceCatalogRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\BroadcastRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\SupportTicketRepositoryInterface;
use App\Modules\SuperAdmin\Domain\Repositories\SaasPackageRepositoryInterface;

use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentDashboardRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentSchoolRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentRegistrationRequestRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentInvoiceRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentSaasModuleRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentGlobalSettingRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentStaffMemberRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentNetworkNodeRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentBackupRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentSystemLogRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentSystemAlertRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentAIModelRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentServiceCatalogRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentBroadcastRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentSupportTicketRepository;
use App\Modules\SuperAdmin\Infrastructure\Repositories\EloquentSaasPackageRepository;

class SuperAdminServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(DashboardRepositoryInterface::class, EloquentDashboardRepository::class);
        $this->app->bind(SchoolRepositoryInterface::class, EloquentSchoolRepository::class);
        $this->app->bind(RegistrationRequestRepositoryInterface::class, EloquentRegistrationRequestRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(SaasModuleRepositoryInterface::class, EloquentSaasModuleRepository::class);
        $this->app->bind(GlobalSettingRepositoryInterface::class, EloquentGlobalSettingRepository::class);
        $this->app->bind(StaffMemberRepositoryInterface::class, EloquentStaffMemberRepository::class);
        $this->app->bind(NetworkNodeRepositoryInterface::class, EloquentNetworkNodeRepository::class);
        $this->app->bind(BackupRepositoryInterface::class, EloquentBackupRepository::class);
        $this->app->bind(SystemLogRepositoryInterface::class, EloquentSystemLogRepository::class);
        $this->app->bind(SystemAlertRepositoryInterface::class, EloquentSystemAlertRepository::class);
        $this->app->bind(AIModelRepositoryInterface::class, EloquentAIModelRepository::class);
        $this->app->bind(ServiceCatalogRepositoryInterface::class, EloquentServiceCatalogRepository::class);
        $this->app->bind(BroadcastRepositoryInterface::class, EloquentBroadcastRepository::class);
        $this->app->bind(SupportTicketRepositoryInterface::class, EloquentSupportTicketRepository::class);
        $this->app->bind(SaasPackageRepositoryInterface::class, EloquentSaasPackageRepository::class);
    }

    public function boot()
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/../Presentation/Views', 'SuperAdmin');

        // Load routes with 'web' middleware
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(__DIR__.'/../Presentation/Routes/web.php');

        // View composer to share system default currency globally across all views
        View::composer('SuperAdmin::*', function ($view) {
            $currency = Cache::remember('system_currency', 60, function () {
                $setting = GlobalSetting::where('key', 'currency')->first();
                $val = $setting ? $setting->value : 'Franc CFA (XOF)';
                if (str_contains($val, 'XOF') || str_contains($val, 'CFA')) return 'FCFA';
                if (str_contains($val, 'EUR') || str_contains($val, 'Euro')) return 'EUR';
                if (str_contains($val, 'USD') || str_contains($val, 'Dollar')) return 'USD';
                if (str_contains($val, 'GNF') || str_contains($val, 'Guinéen')) return 'GNF';
                return 'FCFA';
            });
            $view->with('systemCurrency', $currency);
        });
    }
}
