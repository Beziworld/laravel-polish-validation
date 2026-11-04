<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Rules;

use Beziworld\PolishValidation\Validators\IdCard as IdCardValidator;

/**
 * Validates the series and number of a Polish identity card.
 */
final class IdCard extends IdentifierRule
{
    protected function messageKey(): string
    {
        return 'id_card';
    }

    protected function passes(mixed $value): bool
    {
        return IdCardValidator::isValid($value);
    }
}
