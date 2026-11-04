<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Validators;

/**
 * Polish phone number: nine digits, optionally preceded by +48, 0048 or 48.
 */
final class PhoneNumber
{
    public static function isValid(mixed $value): bool
    {
        return is_string($value) && self::normalize($value) !== null;
    }

    /**
     * Returns the nine-digit national number, or null when the value is not a Polish phone number.
     */
    public static function normalize(string $value): ?string
    {
        if (preg_match('/^[\d\s\-()+]+$/', trim($value)) !== 1) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value) ?? '';

        $national = match (true) {
            str_starts_with(trim($value), '+48') && strlen($digits) === 11 => substr($digits, 2),
            str_starts_with($digits, '0048') && strlen($digits) === 13 => substr($digits, 4),
            str_starts_with($digits, '48') && strlen($digits) === 11 => substr($digits, 2),
            strlen($digits) === 9 && ! str_starts_with(trim($value), '+') => $digits,
            default => null,
        };

        return $national !== null && $national[0] !== '0' ? $national : null;
    }

    /**
     * Formats a valid number in E.164, e.g. "+48500100200".
     */
    public static function toE164(string $value): ?string
    {
        $national = self::normalize($value);

        return $national === null ? null : '+48' . $national;
    }
}
