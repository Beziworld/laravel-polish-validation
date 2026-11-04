<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Rules;

use Beziworld\PolishValidation\Validators\Regon as RegonValidator;

/**
 * Validates a nine- or fourteen-digit REGON number.
 */
final class Regon extends IdentifierRule
{
    protected function messageKey(): string
    {
        return 'regon';
    }

    protected function passes(mixed $value): bool
    {
        return RegonValidator::isValid($value);
    }
}
