<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Support;

/**
 * Weighted checksum helpers shared by the validators.
 *
 * @internal
 */
final class Checksum
{
    /**
     * Sum of the products of digits and weights, aligned from the first character.
     *
     * @param list<int> $weights
     */
    public static function weightedSum(string $digits, array $weights): int
    {
        $sum = 0;

        foreach ($weights as $index => $weight) {
            $sum += ((int) $digits[$index]) * $weight;
        }

        return $sum;
    }

    /**
     * Removes the characters commonly used to format identifiers (spaces, dashes, dots).
     */
    public static function strip(string $value): string
    {
        return preg_replace('/[\s\-.]/u', '', $value) ?? '';
    }

    /**
     * Converts a scalar validation input to a string, or null for unsupported types.
     */
    public static function stringify(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        return is_int($value) ? (string) $value : null;
    }
}
