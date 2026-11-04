# Laravel Polish Validation

Reguły walidacji i helpery Laravela dla polskich identyfikatorów: **PESEL, NIP, REGON, KRS, IBAN/NRB, dowód
osobisty, kod pocztowy i numer telefonu**. Sprawdzane są sumy kontrolne, a nie tylko format.

[English documentation](README.md)

## Instalacja

```bash
composer require beziworld/laravel-polish-validation
php artisan vendor:publish --tag=polish-validation-translations   # opcjonalnie, własne komunikaty
```

Komunikaty są po polsku i angielsku i podążają za językiem aplikacji.

## Reguły tekstowe

```php
$request->validate([
    'pesel'       => ['required', 'pesel'],
    'nip'         => ['required', 'nip'],            // 123-456-32-18, 1234563218, PL1234563218
    'regon'       => ['nullable', 'regon'],          // 9 lub 14 cyfr
    'krs'         => ['nullable', 'krs'],
    'iban'        => ['required', 'iban'],           // dowolny IBAN albo 26-cyfrowy NRB
    'dowod'       => ['required', 'pl_id_card'],     // np. ABA300000
    'kod'         => ['required', 'pl_postal_code'], // 00-950
    'telefon'     => ['required', 'pl_phone'],       // 500100200, +48 500 100 200
]);
```

## Reguły z opcjami

```php
use Beziworld\PolishValidation\Enums\Gender;
use Beziworld\PolishValidation\Rules\{Iban, Nip, Pesel, PostalCode};

$request->validate([
    'pesel'    => ['required', (new Pesel())->adult()],                 // osoba pełnoletnia
    'matka'    => ['required', (new Pesel())->gender(Gender::Female)],
    'nip'      => ['required', (new Nip())->withoutPrefix()],
    'rachunek' => ['required', (new Iban())->countries('PL')->withoutNrb()],
    'kod'      => ['required', (new PostalCode())->lenient()],          // także "00950"
]);
```

## Helpery

```php
$dane = Pesel::parse('44051401359');
$dane->birthDate;   // 1944-05-14
$dane->gender;      // Gender::Male
$dane->age();       // wiek w latach
$dane->isAdult();

Nip::format('1234563218');                  // "123-456-32-18"
Iban::format('61109010140000071219812874'); // "PL61 1090 1014 0000 0712 1981 2874"
PhoneNumber::toE164('500 100 200');         // "+48500100200"
PostalCode::normalize('00950');             // "00-950"
```

## Przykład: rejestracja pacjenta

```php
public function rules(): array
{
    return [
        'pesel' => ['required', 'pesel', 'unique:patients,pesel'],
        'telefon' => ['required', 'pl_phone'],
    ];
}

protected function passedValidation(): void
{
    $dane = Pesel::parse($this->input('pesel'));

    $this->merge([
        'birth_date' => $dane->birthDate->format('Y-m-d'),
        'gender' => $dane->gender->value,
        'telefon' => PhoneNumber::toE164($this->input('telefon')),
    ]);
}
```

W połączeniu z [beziworld/laravel-company-data](https://github.com/beziworld/laravel-company-data) pobierzesz dane
firmy z GUS po NIP i sprawdzisz rachunek na białej liście VAT.

## Autor

Pakiet tworzy i rozwija **Kamil Ćwiertnia**, [beziworld.eu](https://beziworld.eu).

- E-mail: [biuro@beziworld.eu](mailto:biuro@beziworld.eu)
- Zgłoszenia błędów: [GitHub issues](https://github.com/beziworld/laravel-polish-validation/issues)

Potrzebujesz projektu w Laravelu albo integracji z polskimi usługami? Zapraszam do kontaktu przez
[beziworld.eu](https://beziworld.eu).

## Licencja

MIT.
