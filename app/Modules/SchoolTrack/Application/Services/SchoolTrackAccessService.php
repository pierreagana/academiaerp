<?php

namespace App\Modules\SchoolTrack\Application\Services;

use App\Modules\Academic\Domain\Models\ParentAccount;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\SchoolTrack\Domain\Models\SchoolTrackSubscription;
use App\Modules\SuperAdmin\Domain\Models\GlobalSetting;
use InvalidArgumentException;

/**
 * Single source of truth for School Track's paywall, shared by the web
 * dashboard, the mobile API and the SuperAdmin admin screen.
 */
class SchoolTrackAccessService
{
    private const SETTING_KEY = 'school_track_enabled';

    public function isModuleEnabled(): bool
    {
        $value = GlobalSetting::where('key', self::SETTING_KEY)->value('value');

        return $value === null || $value === '1';
    }

    public function setModuleEnabled(bool $enabled): void
    {
        GlobalSetting::updateOrCreate(
            ['key' => self::SETTING_KEY],
            [
                'value' => $enabled ? '1' : '0',
                'type' => 'boolean',
                'is_public' => true,
                'description' => 'Active ou désactive le module School Track pour les parents.',
            ]
        );
    }

    public function subscribe(ParentAccount $parent, string $plan, string $paymentMethod): SchoolTrackSubscription
    {
        if (!array_key_exists($plan, SchoolTrackSubscription::PLAN_PRICES)) {
            throw new InvalidArgumentException("Plan invalide : {$plan}");
        }

        if (!array_key_exists($paymentMethod, Payment::METHODS)) {
            throw new InvalidArgumentException("Moyen de paiement invalide : {$paymentMethod}");
        }

        $now = now();

        return $parent->schoolTrackSubscriptions()->create([
            'plan' => $plan,
            'amount_paid' => SchoolTrackSubscription::PLAN_PRICES[$plan],
            'payment_method' => $paymentMethod,
            'status' => 'active',
            'subscribed_at' => $now,
            'expires_at' => $plan === SchoolTrackSubscription::PLAN_YEARLY
                ? $now->copy()->addYear()
                : $now->copy()->addMonth(),
        ]);
    }

    public function statusFor(?ParentAccount $parent): array
    {
        $subscription = $parent?->activeSchoolTrackSubscription();

        return [
            'moduleEnabled' => $this->isModuleEnabled(),
            'active' => $subscription !== null,
            'plan' => $subscription?->plan,
            'expiresAt' => $subscription?->expires_at?->toIso8601String(),
            'plans' => [
                SchoolTrackSubscription::PLAN_MONTHLY => [
                    'label' => 'Mensuel',
                    'price' => SchoolTrackSubscription::PLAN_PRICES[SchoolTrackSubscription::PLAN_MONTHLY],
                ],
                SchoolTrackSubscription::PLAN_YEARLY => [
                    'label' => 'Annuel',
                    'price' => SchoolTrackSubscription::PLAN_PRICES[SchoolTrackSubscription::PLAN_YEARLY],
                ],
            ],
            'paymentMethods' => Payment::METHODS,
        ];
    }
}
