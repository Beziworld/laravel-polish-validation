<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Rules;

use Beziworld\PolishValidation\Validators\PhoneNumber as PhoneNumberValidator;

/**
 * Validates a Polish phone number with or without the +48 prefix.
 */
final class PhoneNumber extends IdentifierRule
{
    protected function messageKey(): string
    {
        return 'phone';
    }

    protected function passes(mixed $value): bool
    {
        return PhoneNumberValidator::isValid($value);
    }
}
