<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Validators;

use Beziworld\PolishValidation\Exceptions\InvalidIdentifier;
use Beziworld\PolishValidation\Support\Checksum;

/**
 * NIP, the Polish tax identification number.
 *
 * Ten digits with a modulo 11 check digit. Dashes, spaces and the "PL"
 * prefix used in EU VAT numbers are accepted and ignored.
 */
final class Nip
{
    private const array WEIGHTS = [6, 5, 7, 2, 3, 4, 5, 6, 7];

    public static function isValid(mixed $value, bool $allowPrefix = true): bool
    {
        $string = Checksum::stringify($value);

        if ($string === null) {
            return false;
        }

        $nip = self::normalize($string, $allowPrefix);

        if (preg_match('/^\d{10}$/', $nip) !== 1 || $nip === '0000000000') {
            return false;
        }

        $check = Checksum::weightedSum($nip, self::WEIGHTS) % 11;

        return $check !== 10 && $check === (int) $nip[9];
    }

    /**
     * Returns the bare ten digits, without separators and (optionally) the "PL" prefix.
     */
    public static function normalize(string $value, bool $stripPrefix = true): string
    {
        $value = strtoupper(Checksum::strip($value));

        return $stripPrefix && str_starts_with($value, 'PL') ? substr($value, 2) : $value;
    }

    /**
     * Formats a valid NIP as 123-456-78-90.
     *
     * @throws InvalidIdentifier
     */
    public static function format(string $value): string
    {
        if (! self::isValid($value)) {
            throw InvalidIdentifier::of('NIP', $value);
        }

        $nip = self::normalize($value);

        return sprintf('%s-%s-%s-%s', substr($nip, 0, 3), substr($nip, 3, 3), substr($nip, 6, 2), substr($nip, 8, 2));
    }
}
