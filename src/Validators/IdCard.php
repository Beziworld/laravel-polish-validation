<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Validators;

use Beziworld\PolishValidation\Support\Checksum;

/**
 * Series and number of a Polish identity card (dowód osobisty), e.g. "ABA300000".
 *
 * Three letters followed by six digits; the first digit is the check digit.
 * Letters count as 10 (A) to 35 (Z); with the weights 7-3-1-9-7-3-1-7-3 the
 * weighted sum must be divisible by 10.
 */
final class IdCard
{
    private const array WEIGHTS = [7, 3, 1, 9, 7, 3, 1, 7, 3];

    public static function isValid(mixed $value): bool
    {
        $string = Checksum::stringify($value);

        if ($string === null) {
            return false;
        }

        $number = strtoupper(Checksum::strip($string));

        if (preg_match('/^[A-Z]{3}\d{6}$/', $number) !== 1) {
            return false;
        }

        $sum = 0;

        foreach (self::WEIGHTS as $index => $weight) {
            $character = $number[$index];
            $sum += (ctype_alpha($character) ? ord($character) - 55 : (int) $character) * $weight;
        }

        return $sum % 10 === 0;
    }
}
