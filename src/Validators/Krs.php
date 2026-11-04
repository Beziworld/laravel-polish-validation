<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Validators;

use Beziworld\PolishValidation\Support\Checksum;

/**
 * KRS, the National Court Register number: ten digits with leading zeros, without a check digit.
 */
final class Krs
{
    public static function isValid(mixed $value): bool
    {
        $string = Checksum::stringify($value);

        return $string !== null && preg_match('/^\d{10}$/', Checksum::strip($string)) === 1
            && Checksum::strip($string) !== '0000000000';
    }
}
