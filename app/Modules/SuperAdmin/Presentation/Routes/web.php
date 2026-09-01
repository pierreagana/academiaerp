<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SuperAdmin\Presentation\Controllers\DashboardController;
use App\Modules\SuperAdmin\Presentation\Controllers\SchoolController;
use App\Modules\SuperAdmin\Presentation\Controllers\RegistrationRequestController;
use App\Modules\SuperAdmin\Presentation\Controllers\PackageController;
use App\Modules\SuperAdmin\Presentation\Controllers\InvoiceController;
use App\Modules\SuperAdmin\Presentation\Controllers\RevenueAnalysisController;
use App\Modules\SuperAdmin\Presentation\Controllers\SystemLogsController;
use App\Modules\SuperAdmin\Presentation\Controllers\MultiCampusController;
use App\Modules\SuperAdmin\Presentation\Controllers\AiAnalyticsController;
use App\Modules\SuperAdmin\Presentation\Controllers\CmsController;
use App\Modules\SuperAdmin\Presentation\Controllers\SaasModulesController;
use App\Modules\SuperAdmin\Presentation\Controllers\ModuleDetailsController;
use App\Modules\SuperAdmin\Presentation\Controllers\SecurityPermissionsController;
use App\Modules\SuperAdmin\Presentation\Controllers\SupportController;
use App\Modules\SuperAdmin\Presentation\Controllers\GlobalSettingsController;
use App\Modules\SuperAdmin\Presentation\Controllers\SpecificConfigurationController;
use App\Modules\SuperAdmin\Presentation\Controllers\NetworkHealthController;
use App\Modules\SuperAdmin\Presentation\Controllers\SystemAlertsController;
use App\Modules\SuperAdmin\Presentation\Controllers\BackupsController;
use App\Modules\SuperAdmin\Presentation\Controllers\ServiceCatalogController;
use App\Modules\SuperAdmin\Presentation\Controllers\AIModelsController;
use App\Modules\SuperAdmin\Presentation\Controllers\StaffManagementController;
use App\Modules\SuperAdmin\Presentation\Controllers\BroadcastController;
use App\Modules\SuperAdmin\Presentation\Controllers\AuthController;
use App\Modules\SuperAdmin\Presentation\Controllers\LanguageController;
use App\Modules\SuperAdmin\Presentation\Controllers\ExtensionRequestController;
use App\Modules\SuperAdmin\Presentation\Controllers\SchoolTrackAdminController;
use App\Modules\SuperAdmin\Presentation\Controllers\PaymentGatewayController;
use App\Modules\SuperAdmin\Presentation\Controllers\NotificationSettingsController;
use App\Modules\SuperAdmin\Presentation\Controllers\SubscriptionWebhookController;

Route::prefix('superadmin')->group(function () {
    Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('superadmin.lang.switch');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('superadmin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('superadmin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('superadmin.logout');

    Route::middleware('superadmin')->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('superadmin.dashboard');

        // Schools (CRUD)
        Route::get('/schools', [SchoolController::class, 'index'])->name('superadmin.schools');
        Route::get('/schools/{id}', [SchoolController::class, 'show'])->name('superadmin.schools.show');
        Route::post('/schools', [SchoolController::class, 'store'])->name('superadmin.schools.store');
        Route::put('/schools/{id}', [SchoolController::class, 'update'])->name('superadmin.schools.update');
        Route::put('/schools/{id}/group', [SchoolController::class, 'updateGroup'])->name('superadmin.schools.group.update');
        Route::patch('/schools/{id}/suspend', [SchoolController::class, 'suspend'])->name('superadmin.schools.suspend');
        Route::patch('/schools/{id}/activate', [SchoolController::class, 'activate'])->name('superadmin.schools.activate');
        Route::delete('/schools/{id}', [SchoolController::class, 'destroy'])->name('superadmin.schools.destroy');

        // Registration Requests
        Route::get('/registration-requests', [RegistrationRequestController::class, 'index'])->name('superadmin.registration-requests');
        Route::get('/registration-requests/{id}', [RegistrationRequestController::class, 'show'])->name('superadmin.registration-requests.show');
        Route::post('/registration-requests/{id}/approve', [RegistrationRequestController::class, 'approve'])->name('superadmin.registration-requests.approve');
        Route::post('/registration-requests/{id}/reject', [RegistrationRequestController::class, 'reject'])->name('superadmin.registration-requests.reject');

        // Packages (CRUD)
        Route::get('/packages', [PackageController::class, 'index'])->name('superadmin.packages');
        Route::post('/packages', [PackageController::class, 'store'])->name('superadmin.packages.store');
        Route::put('/packages/{id}', [PackageController::class, 'update'])->name('superadmin.packages.update');
        Route::delete('/packages/{id}', [PackageController::class, 'destroy'])->name('superadmin.packages.destroy');

        // Service Catalog
        Route::get('/service-catalog', [ServiceCatalogController::class, 'index'])->name('superadmin.service-catalog');
        Route::post('/service-catalog', [ServiceCatalogController::class, 'store'])->name('superadmin.service-catalog.store');
        Route::post('/service-catalog/{id}/toggle', [ServiceCatalogController::class, 'toggle'])->name('superadmin.service-catalog.toggle');

        // Facilities / Équipements (CRUD)
        Route::get('/facilities', [\App\Modules\SuperAdmin\Presentation\Controllers\FacilityController::class, 'index'])->name('superadmin.facilities');
        Route::post('/facilities', [\App\Modules\SuperAdmin\Presentation\Controllers\FacilityController::class, 'store'])->name('superadmin.facilities.store');
        Route::put('/facilities/{id}', [\App\Modules\SuperAdmin\Presentation\Controllers\FacilityController::class, 'update'])->name('superadmin.facilities.update');
        Route::patch('/facilities/{id}/toggle', [\App\Modules\SuperAdmin\Presentation\Controllers\FacilityController::class, 'toggle'])->name('superadmin.facilities.toggle');
        Route::delete('/facilities/{id}', [\App\Modules\SuperAdmin\Presentation\Controllers\FacilityController::class, 'destroy'])->name('superadmin.facilities.destroy');

        // Invoices
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('superadmin.invoices');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('superadmin.invoices.store');
        Route::post('/invoices/{id}/pay', [InvoiceController::class, 'markAsPaid'])->name('superadmin.invoices.pay');
        Route::post('/invoices/{id}/cancel', [InvoiceController::class, 'cancel'])->name('superadmin.invoices.cancel');
        Route::post('/invoices/{id}/reminder', [InvoiceController::class, 'sendReminder'])->name('superadmin.invoices.reminder');
        Route::post('/invoices/ai-recovery-analysis', [InvoiceController::class, 'aiRecoveryAnalysis'])->name('superadmin.invoices.ai-recovery-analysis');
        Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf'])->name('superadmin.invoices.pdf');

        // Revenue
        Route::get('/revenue', [RevenueAnalysisController::class, 'index'])->name('superadmin.revenue-analysis');
        Route::post('/revenue/ai-forecast', [RevenueAnalysisController::class, 'aiForecast'])->name('superadmin.revenue-analysis.ai-forecast');

        // System Logs
        Route::get('/system-logs', [SystemLogsController::class, 'index'])->name('superadmin.system-logs');
        Route::get('/system-logs/export-csv', [SystemLogsController::class, 'exportCsv'])->name('superadmin.system-logs.export-csv');
        Route::post('/system-logs/ai-audit-summary', [SystemLogsController::class, 'aiAuditSummary'])->name('superadmin.system-logs.ai-audit-summary');

        // Multi-Campus
        Route::get('/multi-campus', [MultiCampusController::class, 'index'])->name('superadmin.multi-campus');
        Route::post('/multi-campus', [MultiCampusController::class, 'store'])->name('superadmin.multi-campus.store');

        // Network & Alerts
        Route::get('/network-health', [NetworkHealthController::class, 'index'])->name('superadmin.network-health');
        Route::get('/system-alerts', [SystemAlertsController::class, 'index'])->name('superadmin.system-alerts');
        Route::patch('/system-alerts/{id}/toggle', [SystemAlertsController::class, 'toggle'])->name('superadmin.system-alerts.toggle');
        Route::delete('/system-alerts/{id}', [SystemAlertsController::class, 'destroy'])->name('superadmin.system-alerts.destroy');

        // Backups
        Route::get('/backups', [BackupsController::class, 'index'])->name('superadmin.backups');
        Route::post('/backups/trigger', [BackupsController::class, 'trigger'])->name('superadmin.backups.trigger');
        Route::post('/backups/settings', [BackupsController::class, 'updateSettings'])->name('superadmin.backups.settings');
        Route::post('/backups/{id}/restore', [BackupsController::class, 'restore'])->name('superadmin.backups.restore');
        Route::get('/backups/{id}/download', [BackupsController::class, 'download'])->name('superadmin.backups.download');
        Route::delete('/backups/{id}', [BackupsController::class, 'delete'])->name('superadmin.backups.delete');

        // Configuration
        Route::get('/specific-configuration', [SpecificConfigurationController::class, 'index'])->name('superadmin.specific-configuration');
        Route::post('/specific-configuration', [SpecificConfigurationController::class, 'update'])->name('superadmin.specific-configuration.update');

        // AI
        Route::get('/ai-analytics', [AiAnalyticsController::class, 'index'])->name('superadmin.ai-analytics');
        Route::get('/ai-models', [AIModelsController::class, 'index'])->name('superadmin.ai-models');
        Route::post('/ai-models', [AIModelsController::class, 'store'])->name('superadmin.ai-models.store');
        Route::post('/ai-models/deploy', [AIModelsController::class, 'deployConfig'])->name('superadmin.ai-models.deploy');
        Route::post('/ai-models/toggle-setting', [AIModelsController::class, 'toggleSetting'])->name('superadmin.ai-models.toggle-setting');
        Route::post('/ai-models/threshold', [AIModelsController::class, 'updateThreshold'])->name('superadmin.ai-models.threshold');
        Route::post('/ai-models/test-connection', [AIModelsController::class, 'testConnection'])->name('superadmin.ai-models.test-connection');
        Route::post('/ai-models/set-provider', [AIModelsController::class, 'setProvider'])->name('superadmin.ai-models.set-provider');
        Route::post('/ai-models/{id}/toggle-status', [AIModelsController::class, 'toggleModelStatus'])->name('superadmin.ai-models.toggle-status');

        // School Track (parent-paid discovery module)
        Route::get('/school-track', [SchoolTrackAdminController::class, 'index'])->name('superadmin.school-track');
        Route::patch('/school-track/toggle', [SchoolTrackAdminController::class, 'toggle'])->name('superadmin.school-track.toggle');

        // Modules SaaS
        Route::get('/modules', [SaasModulesController::class, 'index'])->name('superadmin.modules');
        Route::get('/module-details', [ModuleDetailsController::class, 'index'])->name('superadmin.module-details');
        Route::get('/module-details/{slug}', [ModuleDetailsController::class, 'show'])->name('superadmin.module-details.show');
        Route::post('/module-details/{slug}/price', [ModuleDetailsController::class, 'updatePrice'])->name('superadmin.module-details.update-price');

        // Extension Requests (school-initiated paid module add-ons)
        Route::get('/extension-requests', [ExtensionRequestController::class, 'index'])->name('superadmin.extension-requests');
        Route::post('/extension-requests/{id}/approve', [ExtensionRequestController::class, 'approve'])->name('superadmin.extension-requests.approve');
        Route::post('/extension-requests/{id}/reject', [ExtensionRequestController::class, 'reject'])->name('superadmin.extension-requests.reject');
        Route::get('/plan-change-requests', [\App\Modules\SuperAdmin\Presentation\Controllers\PlanChangeRequestController::class, 'index'])->name('superadmin.plan-change-requests');
        Route::post('/plan-change-requests/{id}/approve', [\App\Modules\SuperAdmin\Presentation\Controllers\PlanChangeRequestController::class, 'approve'])->name('superadmin.plan-change-requests.approve');
        Route::post('/plan-change-requests/{id}/reject', [\App\Modules\SuperAdmin\Presentation\Controllers\PlanChangeRequestController::class, 'reject'])->name('superadmin.plan-change-requests.reject');

        // Security & Permissions
        Route::get('/security-permissions', [SecurityPermissionsController::class, 'index'])->name('superadmin.security-permissions');
        Route::post('/security-permissions', [SecurityPermissionsController::class, 'update'])->name('superadmin.security-permissions.update');
        Route::post('/security-permissions/create-role', [SecurityPermissionsController::class, 'createRole'])->name('superadmin.security-permissions.create-role');
        Route::post('/security-permissions/update-role', [SecurityPermissionsController::class, 'updateRole'])->name('superadmin.security-permissions.update-role');
        Route::post('/security-permissions/role-permissions', [SecurityPermissionsController::class, 'updateRolePermissions'])->name('superadmin.security-permissions.role-permissions');

        // Staff (CRUD & Actions)
        Route::get('/staff', [StaffManagementController::class, 'index'])->name('superadmin.staff');
        Route::post('/staff', [StaffManagementController::class, 'store'])->name('superadmin.staff.store');
        Route::put('/staff/{id}', [StaffManagementController::class, 'update'])->name('superadmin.staff.update');
        Route::post('/staff/{id}/toggle-status', [StaffManagementController::class, 'toggleStatus'])->name('superadmin.staff.toggle-status');
        Route::post('/staff/{id}/reset-password', [StaffManagementController::class, 'resetPassword'])->name('superadmin.staff.reset-password');
        Route::delete('/staff/{id}', [StaffManagementController::class, 'destroy'])->name('superadmin.staff.destroy');

        // Support
        Route::get('/support', [SupportController::class, 'index'])->name('superadmin.support');
        Route::post('/support/{id}/reply', [SupportController::class, 'reply'])->name('superadmin.support.reply');
        Route::post('/support/{id}/close', [SupportController::class, 'close'])->name('superadmin.support.close');
        Route::post('/support/{id}/ai-draft', [SupportController::class, 'generateAiDraft'])->name('superadmin.support.ai-draft');

        // Global Settings
        Route::get('/global-settings', [GlobalSettingsController::class, 'index'])->name('superadmin.global-settings');
        Route::post('/global-settings', [GlobalSettingsController::class, 'update'])->name('superadmin.global-settings.update');
        Route::post('/global-settings/test-smtp', [GlobalSettingsController::class, 'testSmtp'])->name('superadmin.global-settings.test-smtp');

        // Notification Settings (Firebase push)
        Route::get('/notification-settings', [NotificationSettingsController::class, 'index'])->name('superadmin.notification-settings');
        Route::post('/notification-settings', [NotificationSettingsController::class, 'update'])->name('superadmin.notification-settings.update');
        Route::get('/notification-settings/sample', [NotificationSettingsController::class, 'downloadSample'])->name('superadmin.notification-settings.sample');

        // Payment Gateways (SaaS subscription billing)
        Route::get('/payment-gateways', [PaymentGatewayController::class, 'index'])->name('superadmin.payment-gateways');
        Route::put('/payment-gateways/{slug}', [PaymentGatewayController::class, 'update'])->name('superadmin.payment-gateways.update');

        // CMS Management for Landing Page
        Route::get('/cms', [CmsController::class, 'index'])->name('superadmin.cms');
        Route::post('/cms/landing', [CmsController::class, 'updateLandingCms'])->name('superadmin.cms.update-landing');
        Route::post('/cms/faq', [CmsController::class, 'addFaq'])->name('superadmin.cms.add-faq');
        Route::delete('/cms/faq/{index}', [CmsController::class, 'deleteFaq'])->name('superadmin.cms.delete-faq');
        Route::post('/cms/testimonial', [CmsController::class, 'addTestimonial'])->name('superadmin.cms.add-testimonial');
        Route::delete('/cms/testimonial/{index}', [CmsController::class, 'deleteTestimonial'])->name('superadmin.cms.delete-testimonial');

        // Broadcast
        Route::get('/broadcast', [BroadcastController::class, 'index'])->name('superadmin.broadcast');
        Route::post('/broadcast', [BroadcastController::class, 'store'])->name('superadmin.broadcast.store');
        Route::post('/broadcast/ai-rewrite', [BroadcastController::class, 'aiRewrite'])->name('superadmin.broadcast.ai-rewrite');
    });
});

// Public payment gateway webhook receivers (SaaS subscription billing) — no auth,
// called directly by Stripe/Razorpay/PayStack/Flutterwave, CSRF-exempted in bootstrap/app.php.
Route::post('/subscription/webhook/{gateway}', [SubscriptionWebhookController::class, 'handle'])
    ->name('subscription.webhook');
