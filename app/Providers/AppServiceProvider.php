<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Vue Globale support: activeBranchId() returns null to mean "no branch filter, show
        // every branch's data" (used for the cross-branch aggregate view and branch directors
        // are never null). Every `->whereBranch($id)` call site skips the filter when $id is null
        // instead of matching nothing, which a plain `where('branch_id', null)` would do.
        \Illuminate\Database\Eloquent\Builder::macro('whereBranch', function ($branchId) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return is_null($branchId) ? $this : $this->where('branch_id', $branchId);
        });

        // Dynamically apply ALL global settings across the ENTIRE project from global_settings SQL table
        try {
            if (\Schema::hasTable('global_settings')) {
                $allSettings = \App\Modules\SuperAdmin\Domain\Models\GlobalSetting::all()->pluck('value', 'key');

                // 1. SMTP Mail Configuration (Global Project Mailer)
                if ($allSettings->has('smtp_host') && !empty($allSettings->get('smtp_host'))) {
                    config([
                        'mail.default'                 => 'smtp',
                        'mail.mailers.smtp.host'       => $allSettings->get('smtp_host'),
                        'mail.mailers.smtp.port'       => (int) $allSettings->get('smtp_port', 587),
                        'mail.mailers.smtp.username'   => $allSettings->get('smtp_username'),
                        'mail.mailers.smtp.password'   => $allSettings->get('smtp_password'),
                        'mail.mailers.smtp.encryption' => $allSettings->get('smtp_encryption', 'tls'),
                        'mail.from.address'            => $allSettings->get('smtp_from_address', 'noreply@academiaerp.com'),
                        'mail.from.name'               => $allSettings->get('smtp_from_name', 'AcademiaERP SaaS'),
                    ]);
                }

                // 2. Application Name & Identity
                if ($allSettings->has('platform_name')) {
                    config(['app.name' => $allSettings->get('platform_name')]);
                }

                // 3. Language & Locale
                if ($allSettings->has('default_language')) {
                    config(['app.locale' => $allSettings->get('default_language')]);
                    app()->setLocale($allSettings->get('default_language'));
                }

                // 4. Global Timezone
                if ($allSettings->has('timezone')) {
                    config(['app.timezone' => $allSettings->get('timezone')]);
                    date_default_timezone_set($allSettings->get('timezone'));
                }

                // 5. OpenAI / Gemini AI API Key
                if ($allSettings->has('openai_api_key')) {
                    config(['openai.api_key' => $allSettings->get('openai_api_key')]);
                }

                // 6. Stripe Secret Key
                if ($allSettings->has('stripe_secret_key')) {
                    config(['services.stripe.secret' => $allSettings->get('stripe_secret_key')]);
                }

                // 7. Primary Theme Color
                if ($allSettings->has('primary_theme_color')) {
                    config(['theme.primary_color' => $allSettings->get('primary_theme_color')]);
                }

                // 8. Security & 2FA Policies
                if ($allSettings->has('security_2fa_enabled')) {
                    config(['security.2fa_enabled' => filter_var($allSettings->get('security_2fa_enabled'), FILTER_VALIDATE_BOOLEAN)]);
                }
                if ($allSettings->has('security_session_timeout')) {
                    config(['session.lifetime' => (int) $allSettings->get('security_session_timeout')]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore pre-migration or CLI boots
        }
    }
}
