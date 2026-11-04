<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Validators;

use Beziworld\PolishValidation\Support\Checksum;

/**
 * Polish postal code in the "00-950" format.
 */
final class PostalCode
{
    /**
     * @param bool $strict When false, "00950" without the dash is accepted as well.
     */
    public static function isValid(mixed $value, bool $strict = true): bool
    {
        $string = Checksum::stringify($value);

        if ($string === null) {
            return false;
        }

        return preg_match($strict ? '/^\d{2}-\d{3}$/' : '/^\d{2}-?\d{3}$/', trim($string)) === 1;
    }

    /**
     * Formats "00950" or "00-950" as "00-950"; returns null for anything else.
     */
    public static function normalize(string $value): ?string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        return strlen($digits) === 5 ? substr($digits, 0, 2) . '-' . substr($digits, 2) : null;
    }
}
