<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Rules;

use Beziworld\PolishValidation\Validators\Krs as KrsValidator;

/**
 * Validates the format of a KRS number.
 */
final class Krs extends IdentifierRule
{
    protected function messageKey(): string
    {
        return 'krs';
    }

    protected function passes(mixed $value): bool
    {
        return KrsValidator::isValid($value);
    }
}
