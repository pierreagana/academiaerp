<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\GlobalSettingRepositoryInterface;

class GetSpecificConfigurationUseCase
{
    public function __construct(
        private GlobalSettingRepositoryInterface $settingRepository
    ) {}

    public function execute(): array
    {
        $settings = collect($this->settingRepository->getAll())->mapWithKeys(fn($s) => [$s->key => $s->value]);

        return [
            // Localisation & Dates
            'currency'                     => $settings->get('currency', 'Franc CFA (XOF)'),
            'timezone'                     => $settings->get('timezone', 'GMT+0 (Dakar, Abidjan)'),
            'start_month'                  => $settings->get('start_month', 'septembre'),
            'end_year'                     => $settings->get('end_year', 'juin 2025'),

            // Payment Gateways
            'payment_academia_pay'        => $settings->get('payment_academia_pay', '1'),
            'payment_orange_money'        => $settings->get('payment_orange_money', '1'),
            'payment_wave'                => $settings->get('payment_wave', '1'),
            'payment_mtn'                 => $settings->get('payment_mtn', '1'),
            'payment_card'                => $settings->get('payment_card', '0'),
            'fee_payer'                   => $settings->get('fee_payer', 'school'),
            'fee_rate'                    => $settings->get('fee_rate', '1,5'),

            // Alerts Thresholds
            'alert_payment_delay_days'    => $settings->get('alert_payment_delay_days', '15'),
            'alert_server_load_percent'   => $settings->get('alert_server_load_percent', '85'),
            'alert_attendance_drop_percent'=> $settings->get('alert_attendance_drop_percent', '15'),

            // AI & Data Retention
            'ai_experimental_features'    => $settings->get('ai_experimental_features', '0'),
            'ai_predictive_performance'   => $settings->get('ai_predictive_performance', '1'),
            'data_retention_years'        => $settings->get('data_retention_years', '3 Années Scolaires (Recommandé)'),

            // Additional System Limits
            'custom_domain'               => $settings->get('specific_custom_domain', ''),
            'storage_limit'               => $settings->get('specific_storage_limit', '100GB'),
            'api_rate_limit'              => $settings->get('specific_api_rate_limit', 1000),
        ];
    }
}
