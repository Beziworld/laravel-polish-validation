<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Exceptions;

/**
 * Raised when an identifier is parsed although it fails validation.
 */
final class InvalidIdentifier extends PolishValidationException
{
    public static function of(string $type, string $value): self
    {
        return new self(sprintf('"%s" is not a valid %s.', $value, $type));
    }
}
