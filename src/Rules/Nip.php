<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Rules;

use Beziworld\PolishValidation\Validators\Nip as NipValidator;

/**
 * Validates a NIP number; the "PL" prefix is accepted unless disabled.
 */
final class Nip extends IdentifierRule
{
    public function __construct(
        private bool $allowPrefix = true,
    ) {}

    public function withoutPrefix(): self
    {
        $this->allowPrefix = false;

        return $this;
    }

    protected function messageKey(): string
    {
        return 'nip';
    }

    protected function passes(mixed $value): bool
    {
        return NipValidator::isValid($value, $this->allowPrefix);
    }
}
