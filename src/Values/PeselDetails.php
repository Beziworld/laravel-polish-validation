<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Values;

use Beziworld\PolishValidation\Enums\Gender;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Information decoded from a valid PESEL number.
 */
final readonly class PeselDetails
{
    public function __construct(
        public string $number,
        public DateTimeImmutable $birthDate,
        public Gender $gender,
    ) {}

    /**
     * Age in full years on the given day (today by default).
     */
    public function age(?DateTimeInterface $on = null): int
    {
        return $this->birthDate->diff($on ?? new DateTimeImmutable('today'))->y;
    }

    public function isAdult(?DateTimeInterface $on = null): bool
    {
        return $this->age($on) >= 18;
    }
}
