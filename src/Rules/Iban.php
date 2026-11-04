<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Rules;

use Beziworld\PolishValidation\Validators\Iban as IbanValidator;

/**
 * Validates an IBAN or a Polish NRB, optionally restricted to selected countries.
 */
final class Iban extends IdentifierRule
{
    /**
     * @param list<string> $countries
     */
    public function __construct(
        private array $countries = [],
        private bool $allowNrb = true,
    ) {}

    /**
     * Accepts only accounts from the given countries, e.g. countries('PL', 'DE').
     */
    public function countries(string ...$countries): self
    {
        $this->countries = array_values($countries);

        return $this;
    }

    /**
     * Requires the country code; a bare 26-digit NRB is rejected.
     */
    public function withoutNrb(): self
    {
        $this->allowNrb = false;

        return $this;
    }

    protected function messageKey(): string
    {
        return 'iban';
    }

    protected function passes(mixed $value): bool
    {
        return IbanValidator::isValid($value, $this->countries, $this->allowNrb);
    }
}
