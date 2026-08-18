<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;

class SuperAdminLot4Seeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'platform_name',
                'value' => 'Academia ERP SaaS',
                'type' => 'string',
                'description' => 'Global Platform Name',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@academiaerp.com',
                'type' => 'string',
                'description' => 'Global Support Email',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+221 77 123 45 67',
                'type' => 'string',
                'description' => 'Global Contact Phone',
            ],
            [
                'key' => 'openai_api_key',
                'value' => 'sk-1234567890abcdef1234567890abcdef',
                'type' => 'string',
                'description' => 'OpenAI API Key for Global AI Engine',
            ],
            [
                'key' => 'stripe_secret_key',
                'value' => 'rk_test_123456789',
                'type' => 'string',
                'description' => 'Stripe Secret Key for International Payments',
            ],
            [
                'key' => 'orange_money_api',
                'value' => '',
                'type' => 'string',
                'description' => 'Orange Money API for Mobile Money Integration',
            ],
            [
                'key' => 'last_automated_backup',
                'value' => '2023-10-27 03:00:00',
                'type' => 'datetime',
                'description' => 'Last Automated Backup time',
            ],
            [
                'key' => 'force_2fa',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Force 2FA for staff',
            ],
            [
                'key' => 'session_timeout',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Session Timeout in minutes',
            ],
            [
                'key' => 'ip_restriction',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'IP Restriction for Admin access',
            ],
        ];

        foreach ($settings as $setting) {
            GlobalSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
