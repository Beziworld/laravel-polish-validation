# Laravel Polish Validation

[![tests](https://github.com/beziworld/laravel-polish-validation/actions/workflows/tests.yml/badge.svg)](https://github.com/beziworld/laravel-polish-validation/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/beziworld/laravel-polish-validation.svg)](https://packagist.org/packages/beziworld/laravel-polish-validation)
[![License](https://img.shields.io/packagist/l/beziworld/laravel-polish-validation.svg)](LICENSE.md)

Laravel validation rules and helpers for Polish identifiers: **PESEL, NIP, REGON, KRS, IBAN/NRB, identity card,
postal code and phone number**. Checksums are verified, not only the format.

[Polska wersja dokumentacji](README.pl.md)

## Installation

```bash
composer require beziworld/laravel-polish-validation
php artisan vendor:publish --tag=polish-validation-translations   # optional, to customise messages
```

Messages are included in English and Polish and follow the application locale.

## String rules

```php
$request->validate([
    'pesel'       => ['required', 'pesel'],
    'nip'         => ['required', 'nip'],            // accepts 123-456-32-18, 1234563218, PL1234563218
    'regon'       => ['nullable', 'regon'],          // 9 or 14 digits
    'krs'         => ['nullable', 'krs'],
    'iban'        => ['required', 'iban'],           // any IBAN or a 26-digit Polish NRB
    'id_card'     => ['required', 'pl_id_card'],     // e.g. ABA300000
    'postal_code' => ['required', 'pl_postal_code'], // 00-950
    'phone'       => ['required', 'pl_phone'],       // 500100200, +48 500 100 200, 0048500100200
]);
```

## Rule objects with options

```php
use Beziworld\PolishValidation\Enums\Gender;
use Beziworld\PolishValidation\Rules\Iban;
use Beziworld\PolishValidation\Rules\Nip;
use Beziworld\PolishValidation\Rules\Pesel;
use Beziworld\PolishValidation\Rules\PostalCode;

$request->validate([
    'pesel'   => ['required', (new Pesel())->adult()],                 // at least 18 years old today
    'mother'  => ['required', (new Pesel())->gender(Gender::Female)],
    'nip'     => ['required', (new Nip())->withoutPrefix()],           // reject "PL1234563218"
    'account' => ['required', (new Iban())->countries('PL', 'DE')->withoutNrb()],
    'zip'     => ['required', (new PostalCode())->lenient()],          // also accept "00950"
]);
```

## Helpers outside validation

```php
use Beziworld\PolishValidation\Validators\Iban;
use Beziworld\PolishValidation\Validators\Nip;
use Beziworld\PolishValidation\Validators\Pesel;
use Beziworld\PolishValidation\Validators\PhoneNumber;
use Beziworld\PolishValidation\Validators\PostalCode;

$details = Pesel::parse('44051401359');   // throws InvalidIdentifier when invalid; Pesel::details() returns null
$details->birthDate;                       // DateTimeImmutable 1944-05-14
$details->gender;                          // Gender::Male
$details->age();                           // 82
$details->isAdult();                       // true

Nip::normalize('PL 123-456-32-18');        // "1234563218"
Nip::format('1234563218');                 // "123-456-32-18"
Iban::format('61109010140000071219812874');// "PL61 1090 1014 0000 0712 1981 2874"
PhoneNumber::toE164('500 100 200');        // "+48500100200"
PostalCode::normalize('00950');            // "00-950"
```

## Usage examples

### Registration form with automatic data from PESEL

```php
class RegisterPatientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pesel' => ['required', 'pesel', 'unique:patients,pesel'],
            'phone' => ['required', 'pl_phone'],
            'postal_code' => ['required', 'pl_postal_code'],
        ];
    }

    protected function passedValidation(): void
    {
        $details = Pesel::parse($this->input('pesel'));

        $this->merge([
            'birth_date' => $details->birthDate->format('Y-m-d'),
            'gender' => $details->gender->value,
            'phone' => PhoneNumber::toE164($this->input('phone')),
        ]);
    }
}
```

### B2B checkout

```php
$request->validate([
    'company.nip' => ['required_if:invoice,1', 'nip'],
    'company.regon' => ['nullable', 'regon'],
    'refund_account' => ['nullable', (new Iban())->countries('PL')],
]);

$order->company_nip = Nip::normalize($request->input('company.nip'));
```

Combine it with [beziworld/laravel-company-data](https://github.com/beziworld/laravel-company-data) to fetch the
company name and address from GUS by NIP and to check the account on the VAT white list.

## Algorithms

| Identifier | Check |
|---|---|
| PESEL | 11 digits, weights 1-3-7-9, valid date of birth (1800-2299) |
| NIP | 10 digits, weights 6-5-7-2-3-4-5-6-7, modulo 11 |
| REGON | 9 digits (weights 8-9-2-3-4-5-6-7) or 14 digits (parent REGON + weights 2-4-8-5-0-9-7-3-6-1-2-4-8) |
| KRS | 10 digits (no check digit exists) |
| IBAN | country length table + ISO 7064 MOD 97-10 |
| Identity card | 3 letters + 6 digits, weights 7-3-1-9-7-3-1-7-3 |

## Development

```bash
composer install
composer ci   # pint --test, phpstan (level 8), pest
```

## Author

Created and maintained by **Kamil Ćwiertnia**, [beziworld.eu](https://beziworld.eu).

- E-mail: [biuro@beziworld.eu](mailto:biuro@beziworld.eu)
- Issues and feature requests: [GitHub issues](https://github.com/beziworld/laravel-polish-validation/issues)
- Security vulnerabilities: see [SECURITY.md](SECURITY.md)

Need a custom Laravel project or an integration with Polish services? Get in touch through
[beziworld.eu](https://beziworld.eu).

### Other packages by the author

- [beziworld/laravel-company-data](https://github.com/beziworld/laravel-company-data): GUS, VAT white list, VIES
- [beziworld/laravel-polish-calendar](https://github.com/beziworld/laravel-polish-calendar): public holidays and business days
- [beziworld/laravel-nbp](https://github.com/beziworld/laravel-nbp): NBP exchange rates
- [beziworld/laravel-przelewy24](https://github.com/beziworld/laravel-przelewy24), [laravel-tpay](https://github.com/beziworld/laravel-tpay): payments
- [beziworld/laravel-smsapi](https://github.com/beziworld/laravel-smsapi), [laravel-directadmin](https://github.com/beziworld/laravel-directadmin)

## License

MIT. See [LICENSE.md](LICENSE.md).
