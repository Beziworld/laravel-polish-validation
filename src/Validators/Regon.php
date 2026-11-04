<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Validators;

use Beziworld\PolishValidation\Support\Checksum;

/**
 * REGON, the statistical number of the National Business Registry.
 *
 * Nine digits for entities, fourteen digits for local units; the latter
 * start with the nine-digit number of the parent entity, which must be
 * valid as well.
 */
final class Regon
{
    private const array WEIGHTS_9 = [8, 9, 2, 3, 4, 5, 6, 7];

    private const array WEIGHTS_14 = [2, 4, 8, 5, 0, 9, 7, 3, 6, 1, 2, 4, 8];

    public static function isValid(mixed $value): bool
    {
        $string = Checksum::stringify($value);

        if ($string === null) {
            return false;
        }

        $regon = Checksum::strip($string);

        return match (true) {
            preg_match('/^\d{9}$/', $regon) === 1 => self::checkDigitMatches($regon, self::WEIGHTS_9),
            preg_match('/^\d{14}$/', $regon) === 1 => self::checkDigitMatches(substr($regon, 0, 9), self::WEIGHTS_9)
                && self::checkDigitMatches($regon, self::WEIGHTS_14),
            default => false,
        };
    }

    /**
     * @param list<int> $weights
     */
    private static function checkDigitMatches(string $regon, array $weights): bool
    {
        $check = Checksum::weightedSum($regon, $weights) % 11;

        return ($check === 10 ? 0 : $check) === (int) $regon[count($weights)];
    }
}
