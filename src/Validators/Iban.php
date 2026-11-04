<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Validators;

use Beziworld\PolishValidation\Exceptions\InvalidIdentifier;
use Beziworld\PolishValidation\Support\Checksum;

/**
 * IBAN and the Polish domestic account number (NRB).
 *
 * The country code determines the expected length; the check digits are
 * verified with the ISO 7064 MOD 97-10 algorithm. A 26-digit NRB is treated
 * as a Polish IBAN without the "PL" prefix when allowed.
 */
final class Iban
{
    /**
     * IBAN lengths per country (ISO 13616 registry).
     */
    private const array LENGTHS = [
        'AD' => 24, 'AE' => 23, 'AL' => 28, 'AT' => 20, 'AZ' => 28, 'BA' => 20, 'BE' => 16, 'BG' => 22, 'BH' => 22,
        'BR' => 29, 'BY' => 28, 'CH' => 21, 'CR' => 22, 'CY' => 28, 'CZ' => 24, 'DE' => 22, 'DK' => 18, 'DO' => 28,
        'EE' => 20, 'EG' => 29, 'ES' => 24, 'FI' => 18, 'FO' => 18, 'FR' => 27, 'GB' => 22, 'GE' => 22, 'GI' => 23,
        'GL' => 18, 'GR' => 27, 'GT' => 28, 'HR' => 21, 'HU' => 28, 'IE' => 22, 'IL' => 23, 'IQ' => 23, 'IS' => 26,
        'IT' => 27, 'JO' => 30, 'KW' => 30, 'KZ' => 20, 'LB' => 28, 'LC' => 32, 'LI' => 21, 'LT' => 20, 'LU' => 20,
        'LV' => 21, 'MC' => 27, 'MD' => 24, 'ME' => 22, 'MK' => 19, 'MR' => 27, 'MT' => 31, 'MU' => 30, 'NL' => 18,
        'NO' => 15, 'PK' => 24, 'PL' => 28, 'PS' => 29, 'PT' => 25, 'QA' => 29, 'RO' => 24, 'RS' => 22, 'SA' => 24,
        'SC' => 31, 'SE' => 24, 'SI' => 19, 'SK' => 24, 'SM' => 27, 'ST' => 25, 'SV' => 28, 'TL' => 23, 'TN' => 24,
        'TR' => 26, 'UA' => 29, 'VA' => 22, 'VG' => 24, 'XK' => 20,
    ];

    /**
     * @param list<string> $countries Accepted country codes; empty accepts every country.
     * @param bool $allowNrb Accepts a 26-digit Polish account number without the country code.
     */
    public static function isValid(mixed $value, array $countries = [], bool $allowNrb = true): bool
    {
        $string = Checksum::stringify($value);

        if ($string === null) {
            return false;
        }

        $iban = self::normalize($string, $allowNrb);

        if (preg_match('/^([A-Z]{2})(\d{2})([A-Z0-9]+)$/', $iban, $matches) !== 1) {
            return false;
        }

        $country = $matches[1];

        if (($countries !== [] && ! in_array($country, array_map(strtoupper(...), $countries), true))
            || (self::LENGTHS[$country] ?? null) !== strlen($iban)) {
            return false;
        }

        return self::mod97(substr($iban, 4) . substr($iban, 0, 4)) === 1;
    }

    /**
     * Uppercase IBAN without separators; a bare 26-digit NRB gets the "PL" prefix.
     */
    public static function normalize(string $value, bool $allowNrb = true): string
    {
        $iban = strtoupper(Checksum::strip($value));

        return $allowNrb && preg_match('/^\d{26}$/', $iban) === 1 ? 'PL' . $iban : $iban;
    }

    /**
     * Formats a valid IBAN in groups of four characters, e.g. "PL61 1090 1014 0000 0712 1981 2874".
     *
     * @throws InvalidIdentifier
     */
    public static function format(string $value): string
    {
        if (! self::isValid($value)) {
            throw InvalidIdentifier::of('IBAN', $value);
        }

        return trim(chunk_split(self::normalize($value), 4, ' '));
    }

    private static function mod97(string $rearranged): int
    {
        $numeric = '';

        foreach (str_split($rearranged) as $character) {
            $numeric .= ctype_alpha($character) ? (string) (ord($character) - 55) : $character;
        }

        $remainder = 0;

        foreach (str_split($numeric, 7) as $chunk) {
            $remainder = (int) ($remainder . $chunk) % 97;
        }

        return $remainder;
    }
}
