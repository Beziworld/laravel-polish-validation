<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Validators;

use Beziworld\PolishValidation\Enums\Gender;
use Beziworld\PolishValidation\Exceptions\InvalidIdentifier;
use Beziworld\PolishValidation\Support\Checksum;
use Beziworld\PolishValidation\Values\PeselDetails;
use DateTimeImmutable;

/**
 * PESEL, the Polish personal identification number.
 *
 * Eleven digits: date of birth (YYMMDD with the century encoded in the
 * month), a serial number whose last digit encodes the gender, and a check
 * digit computed with the weights 1-3-7-9.
 */
final class Pesel
{
    private const array WEIGHTS = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];

    /**
     * Month offsets mapped to the century they encode.
     */
    private const array CENTURIES = [80 => 1800, 0 => 1900, 20 => 2000, 40 => 2100, 60 => 2200];

    public static function isValid(mixed $value): bool
    {
        return self::details($value) !== null;
    }

    /**
     * Decodes the date of birth and gender, or returns null for an invalid number.
     */
    public static function details(mixed $value): ?PeselDetails
    {
        $pesel = Checksum::stringify($value);

        if ($pesel === null || preg_match('/^\d{11}$/', $pesel) !== 1) {
            return null;
        }

        $check = (10 - Checksum::weightedSum($pesel, self::WEIGHTS) % 10) % 10;

        if ($check !== (int) $pesel[10]) {
            return null;
        }

        $birthDate = self::birthDate($pesel);

        if ($birthDate === null) {
            return null;
        }

        return new PeselDetails(
            number: $pesel,
            birthDate: $birthDate,
            gender: ((int) $pesel[9]) % 2 === 1 ? Gender::Male : Gender::Female,
        );
    }

    /**
     * @throws InvalidIdentifier
     */
    public static function parse(string $value): PeselDetails
    {
        return self::details($value) ?? throw InvalidIdentifier::of('PESEL', $value);
    }

    private static function birthDate(string $pesel): ?DateTimeImmutable
    {
        $year = (int) substr($pesel, 0, 2);
        $encodedMonth = (int) substr($pesel, 2, 2);
        $day = (int) substr($pesel, 4, 2);

        $offset = intdiv($encodedMonth, 20) * 20;
        $century = self::CENTURIES[$offset] ?? null;
        $month = $encodedMonth - $offset;

        if ($century === null || ! checkdate($month, $day, $century + $year)) {
            return null;
        }

        return new DateTimeImmutable(sprintf('%04d-%02d-%02d 00:00:00', $century + $year, $month, $day));
    }
}
