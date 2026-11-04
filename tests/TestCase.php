<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Tests;

use Beziworld\PolishValidation\PolishValidationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [PolishValidationServiceProvider::class];
    }

    /**
     * Builds a PESEL with a correct check digit, independently of the validator under test.
     */
    public static function pesel(int $year, int $month, int $day, string $serial = '0123'): string
    {
        $monthOffset = match (intdiv($year, 100)) {
            18 => 80,
            19 => 0,
            20 => 20,
            21 => 40,
            default => 60,
        };

        $base = sprintf('%02d%02d%02d%s', $year % 100, $month + $monthOffset, $day, $serial);
        $weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
        $sum = 0;

        foreach (str_split($base) as $index => $digit) {
            $sum += (int) $digit * $weights[$index];
        }

        return $base . ((10 - $sum % 10) % 10);
    }
}
