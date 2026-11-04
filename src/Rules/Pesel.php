<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Rules;

use Beziworld\PolishValidation\Enums\Gender;
use Beziworld\PolishValidation\Validators\Pesel as PeselValidator;
use Closure;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Validates a PESEL number, optionally requiring a gender or an adult person.
 */
final class Pesel extends IdentifierRule
{
    private ?Gender $gender = null;

    private bool $adult = false;

    public function gender(Gender $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Requires the person to be at least 18 years old today.
     */
    public function adult(): self
    {
        $this->adult = true;

        return $this;
    }

    /**
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $details = PeselValidator::details($value);

        if ($details === null) {
            $fail('polish-validation::validation.pesel')->translate();

            return;
        }

        if ($this->gender !== null && $details->gender !== $this->gender) {
            $fail('polish-validation::validation.pesel_gender')->translate();
        }

        if ($this->adult && ! $details->isAdult()) {
            $fail('polish-validation::validation.pesel_adult')->translate();
        }
    }

    protected function messageKey(): string
    {
        return 'pesel';
    }

    protected function passes(mixed $value): bool
    {
        return PeselValidator::isValid($value);
    }
}
