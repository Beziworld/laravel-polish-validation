<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation;

use Beziworld\PolishValidation\Validators\Iban;
use Beziworld\PolishValidation\Validators\IdCard;
use Beziworld\PolishValidation\Validators\Krs;
use Beziworld\PolishValidation\Validators\Nip;
use Beziworld\PolishValidation\Validators\Pesel;
use Beziworld\PolishValidation\Validators\PhoneNumber;
use Beziworld\PolishValidation\Validators\PostalCode;
use Beziworld\PolishValidation\Validators\Regon;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;

/**
 * Registers the string validation rules ("pesel", "nip", ...) and their translations.
 */
final class PolishValidationServiceProvider extends ServiceProvider
{
    /**
     * String rule names mapped to their validators and message keys.
     *
     * @var array<string, array{0: callable(mixed): bool, 1: string}>
     */
    private const array RULES = [
        'pesel' => [[Pesel::class, 'isValid'], 'pesel'],
        'nip' => [[Nip::class, 'isValid'], 'nip'],
        'regon' => [[Regon::class, 'isValid'], 'regon'],
        'krs' => [[Krs::class, 'isValid'], 'krs'],
        'iban' => [[Iban::class, 'isValid'], 'iban'],
        'pl_id_card' => [[IdCard::class, 'isValid'], 'id_card'],
        'pl_postal_code' => [[PostalCode::class, 'isValid'], 'postal_code'],
        'pl_phone' => [[PhoneNumber::class, 'isValid'], 'phone'],
    ];

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'polish-validation');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/polish-validation'),
            ], 'polish-validation-translations');
        }

        $this->callAfterResolving('validator', function (Factory $validator): void {
            foreach (self::RULES as $name => [$callback, $messageKey]) {
                $sentinel = 'polish-validation::validation.' . $messageKey;

                $validator->extend($name, static fn(string $attribute, mixed $value): bool => $callback($value), $sentinel);

                // The fallback message is translated lazily so that the current locale is used and
                // application-level messages (validation.<rule>) keep precedence.
                $validator->replacer($name, function (string $message, string $attribute, string $rule, array $parameters, Validator $instance) use ($sentinel): string {
                    if ($message !== $sentinel) {
                        return $message;
                    }

                    return $this->app->make(Translator::class)->get($sentinel, [
                        'attribute' => $instance->getDisplayableAttribute($attribute),
                    ]);
                });
            }
        });
    }
}
