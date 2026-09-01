<?php

namespace App\Support\Payments;

class CurrencyCode
{
    /**
     * The platform's `GlobalSetting` currency value is a display label
     * ("Franc CFA (XOF)", "FCFA", "Dollar US"...), never an ISO 4217 code —
     * gateway APIs need the real code. Same substring convention already used
     * in AppServiceProvider/SuperAdminServiceProvider for the display label.
     */
    public static function iso(string $displayCurrency): string
    {
        if (str_contains($displayCurrency, 'XOF') || str_contains($displayCurrency, 'CFA')) {
            return 'XOF';
        }
        if (str_contains($displayCurrency, 'GNF') || str_contains($displayCurrency, 'Guinéen')) {
            return 'GNF';
        }
        if (str_contains($displayCurrency, 'EUR') || str_contains($displayCurrency, 'Euro')) {
            return 'EUR';
        }
        if (str_contains($displayCurrency, 'USD') || str_contains($displayCurrency, 'Dollar')) {
            return 'USD';
        }

        return 'XOF';
    }

    /**
     * Whether this ISO currency has no minor/subunit (matches Stripe's
     * "zero-decimal currency" list) — XOF and GNF have no centimes.
     */
    public static function isZeroDecimal(string $isoCode): bool
    {
        return in_array($isoCode, ['XOF', 'GNF', 'XAF'], true);
    }

    /**
     * The integer "smallest unit" amount Stripe/PayStack/Razorpay expect
     * (cents for USD/EUR, the amount itself unchanged for zero-decimal XOF/GNF).
     */
    public static function toSmallestUnit(float $amount, string $isoCode): int
    {
        return self::isZeroDecimal($isoCode) ? (int) round($amount) : (int) round($amount * 100);
    }
}
