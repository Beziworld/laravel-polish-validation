<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Rules;

use Beziworld\PolishValidation\Validators\PostalCode as PostalCodeValidator;

/**
 * Validates a Polish postal code ("00-950", or also "00950" in lenient mode).
 */
final class PostalCode extends IdentifierRule
{
    public function __construct(
        private bool $strict = true,
    ) {}

    public function lenient(): self
    {
        $this->strict = false;

        return $this;
    }

    protected function messageKey(): string
    {
        return 'postal_code';
    }

    protected function passes(mixed $value): bool
    {
        return PostalCodeValidator::isValid($value, $this->strict);
    }
}
