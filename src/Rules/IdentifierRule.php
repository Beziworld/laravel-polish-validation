<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Base class of the identifier rules: validates and reports a translated message.
 */
abstract class IdentifierRule implements ValidationRule
{
    /**
     * Translation key of the failure message, relative to the package namespace.
     */
    abstract protected function messageKey(): string;

    abstract protected function passes(mixed $value): bool;

    /**
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->passes($value)) {
            $fail('polish-validation::validation.' . $this->messageKey())->translate();
        }
    }
}
