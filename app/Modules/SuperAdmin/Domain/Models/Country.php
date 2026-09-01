<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'iso2', 'dial_code', 'flag_emoji', 'order'];

    public $timestamps = true;

    /** Splits a stored "+225 0102030405" value back into [code, number] for pre-filling an edit form's selector+input pair. */
    public static function splitPhone(?string $phone, string $defaultCode = '+225'): array
    {
        if (empty($phone)) {
            return [$defaultCode, ''];
        }

        $parts = explode(' ', $phone, 2);

        return count($parts) === 2 && str_starts_with($parts[0], '+') ? $parts : [$defaultCode, $phone];
    }

    /** Combines a selected dial code + typed number into the single stored string, or null when nothing was typed. */
    public static function combinePhone(?string $code, ?string $number, string $defaultCode = '+225'): ?string
    {
        if (empty($number)) {
            return null;
        }

        return ($code ?: $defaultCode) . ' ' . trim($number);
    }

    /**
     * Digits-only form of a phone number, stripping any "+225 " country-code
     * prefix, spaces, dashes. Used to match a phone across the two storage
     * eras this app has had: bare local digits (pre-country-code UI) and the
     * new "+225 0102030405" combined format — both eras must keep matching
     * each other for parent login, cross-school account joining, and child
     * claiming to keep working without a data migration.
     */
    public static function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D/', '', (string) $phone) ?? '';
    }

    /** True when two phone values represent the same number regardless of which storage era either one is in. */
    public static function phonesMatch(?string $a, ?string $b): bool
    {
        $na = self::normalizePhone($a);
        $nb = self::normalizePhone($b);

        if ($na === '' || $nb === '') {
            return false;
        }

        return $na === $nb || str_ends_with($na, $nb) || str_ends_with($nb, $na);
    }

    /**
     * Applies a "match this phone regardless of storage era" condition to a
     * query — digits-only suffix comparison via SQL so it stays index-usable-ish
     * and doesn't require pulling the whole table into PHP to compare.
     */
    public static function applyPhoneMatch($query, string $column, ?string $phone)
    {
        $normalized = self::normalizePhone($phone);
        if ($normalized === '') {
            return $query->whereRaw('1 = 0');
        }

        $digitsOnlySql = "REPLACE(REPLACE(REPLACE({$column}, ' ', ''), '+', ''), '-', '')";

        // Bidirectional suffix match: either side's digits may carry the country-code
        // prefix the other one lacks (old bare-digits era vs new "+225 ..." era), so
        // a one-directional LIKE only catches half of the real mixed-era cases.
        return $query->whereRaw(
            "{$digitsOnlySql} != '' AND ({$digitsOnlySql} LIKE ? OR ? LIKE CONCAT('%', {$digitsOnlySql}))",
            ['%' . $normalized, $normalized]
        );
    }
}
