<?php

declare(strict_types=1);

use Beziworld\PolishValidation\Enums\Gender;
use Beziworld\PolishValidation\Rules\Iban;
use Beziworld\PolishValidation\Rules\IdCard;
use Beziworld\PolishValidation\Rules\Krs;
use Beziworld\PolishValidation\Rules\Nip;
use Beziworld\PolishValidation\Rules\Pesel;
use Beziworld\PolishValidation\Rules\PhoneNumber;
use Beziworld\PolishValidation\Rules\PostalCode;
use Beziworld\PolishValidation\Rules\Regon;
use Beziworld\PolishValidation\Tests\TestCase;
use Illuminate\Support\Facades\Validator;

/**
 * @param array<string, mixed> $data
 * @param array<string, mixed> $rules
 * @return array<string, list<string>>
 */
function errors(array $data, array $rules): array
{
    return Validator::make($data, $rules)->errors()->toArray();
}

it('provides string rules', function (string $rule, string $valid, string $invalid): void {
    expect(errors(['value' => $valid], ['value' => $rule]))->toBe([])
        ->and(errors(['value' => $invalid], ['value' => $rule]))->toHaveKey('value');
})->with([
    ['pesel', '44051401359', '44051401358'],
    ['nip', '123-456-32-18', '123-456-32-17'],
    ['regon', '123456785', '123456786'],
    ['krs', '0000123456', '123'],
    ['iban', 'PL61109010140000071219812874', 'PL00109010140000071219812874'],
    ['pl_id_card', 'ABA300000', 'ABA400000'],
    ['pl_postal_code', '00-950', '00950'],
    ['pl_phone', '+48500100200', '123'],
]);

it('translates string rule messages to the current locale', function (): void {
    app()->setLocale('en');
    expect(errors(['tax_id' => '1'], ['tax_id' => 'nip'])['tax_id'][0])->toBe('The tax id must be a valid NIP number.');

    app()->setLocale('pl');
    expect(errors(['tax_id' => '1'], ['tax_id' => 'nip'])['tax_id'][0])->toBe('Pole tax id musi zawierać poprawny numer NIP.');
});

it('lets application messages override the package messages', function (): void {
    $messages = Validator::make(['nip' => '1'], ['nip' => 'nip'], ['nip.nip' => 'Custom message'])->errors()->all();

    expect($messages)->toBe(['Custom message']);
});

it('provides rule objects with options', function (): void {
    $female = TestCase::pesel(1990, 1, 1, '0002');
    $child = TestCase::pesel((int) date('Y') - 5, 1, 1, '0003');

    expect(errors(['p' => $female], ['p' => [(new Pesel())->gender(Gender::Female)]]))->toBe([])
        ->and(errors(['p' => $female], ['p' => [(new Pesel())->gender(Gender::Male)]])['p'][0])->toContain('gender')
        ->and(errors(['p' => $child], ['p' => [(new Pesel())->adult()]])['p'][0])->toContain('adult')
        ->and(errors(['p' => 'x'], ['p' => [new Pesel()]])['p'][0])->toContain('PESEL')
        ->and(errors(['n' => 'PL1234563218'], ['n' => [(new Nip())->withoutPrefix()]]))->toHaveKey('n')
        ->and(errors(['i' => 'DE89370400440532013000'], ['i' => [(new Iban())->countries('PL')]]))->toHaveKey('i')
        ->and(errors(['i' => '61109010140000071219812874'], ['i' => [(new Iban())->withoutNrb()]]))->toHaveKey('i')
        ->and(errors(['c' => '00950'], ['c' => [(new PostalCode())->lenient()]]))->toBe([])
        ->and(errors(['r' => '123456785', 'k' => '0000123456', 'd' => 'ABA300000', 't' => '500100200'], [
            'r' => [new Regon()], 'k' => [new Krs()], 'd' => [new IdCard()], 't' => [new PhoneNumber()],
        ]))->toBe([]);
});
