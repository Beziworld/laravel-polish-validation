<?php

declare(strict_types=1);

use Beziworld\PolishValidation\Enums\Gender;
use Beziworld\PolishValidation\Exceptions\InvalidIdentifier;
use Beziworld\PolishValidation\Tests\TestCase;
use Beziworld\PolishValidation\Validators\Iban;
use Beziworld\PolishValidation\Validators\IdCard;
use Beziworld\PolishValidation\Validators\Krs;
use Beziworld\PolishValidation\Validators\Nip;
use Beziworld\PolishValidation\Validators\Pesel;
use Beziworld\PolishValidation\Validators\PhoneNumber;
use Beziworld\PolishValidation\Validators\PostalCode;
use Beziworld\PolishValidation\Validators\Regon;

describe('PESEL', function (): void {
    it('accepts a known valid number and decodes it', function (): void {
        $details = Pesel::parse('44051401359');

        expect($details->birthDate->format('Y-m-d'))->toBe('1944-05-14')
            ->and($details->gender)->toBe(Gender::Male)
            ->and($details->age(new DateTimeImmutable('2026-05-14')))->toBe(82)
            ->and($details->isAdult())->toBeTrue();
    });

    it('decodes every century', function (int $year): void {
        expect(Pesel::details(TestCase::pesel($year, 2, 28))?->birthDate->format('Y-m-d'))->toBe(sprintf('%04d-02-28', $year));
    })->with([1850, 1999, 2000, 2024, 2150, 2250]);

    it('decodes the gender from the tenth digit', function (): void {
        expect(Pesel::details(TestCase::pesel(1990, 1, 1, '0002'))?->gender)->toBe(Gender::Female)
            ->and(Pesel::details(TestCase::pesel(1990, 1, 1, '0003'))?->gender)->toBe(Gender::Male);
    });

    it('rejects invalid numbers', function (mixed $value): void {
        expect(Pesel::isValid($value))->toBeFalse();
    })->with([
        'wrong check digit' => '44051401358',
        'impossible date' => TestCase::pesel(2023, 2, 29),
        'too short' => '4405140135',
        'letters' => '4405140135a',
        'zeros' => '00000000000',
        'null' => null,
        'array' => [['44051401359']],
    ]);

    it('accepts integer input', function (): void {
        expect(Pesel::isValid(44051401359))->toBeTrue();
    });

    it('throws when parsing an invalid number', function (): void {
        expect(fn() => Pesel::parse('123'))->toThrow(InvalidIdentifier::class, '"123" is not a valid PESEL.');
    });
});

describe('NIP', function (): void {
    it('accepts valid numbers in common notations', function (string $nip): void {
        expect(Nip::isValid($nip))->toBeTrue();
    })->with(['1234563218', '123-456-32-18', '123 456 32 18', 'PL1234563218', 'pl 123-456-32-18']);

    it('rejects invalid numbers', function (mixed $nip): void {
        expect(Nip::isValid($nip))->toBeFalse();
    })->with(['1234563217', '0000000000', '123456321', 'DE1234563218', null]);

    it('can require the number without prefix', function (): void {
        expect(Nip::isValid('PL1234563218', allowPrefix: false))->toBeFalse();
    });

    it('normalizes and formats', function (): void {
        expect(Nip::normalize('PL 123-456-32-18'))->toBe('1234563218')
            ->and(Nip::format('1234563218'))->toBe('123-456-32-18')
            ->and(fn() => Nip::format('1'))->toThrow(InvalidIdentifier::class);
    });
});

describe('REGON', function (): void {
    it('validates nine and fourteen digit numbers', function (string $regon, bool $valid): void {
        expect(Regon::isValid($regon))->toBe($valid);
    })->with([
        ['123456785', true],
        ['123-456-785', true],
        ['123456786', false],
        ['12345678512347', true],
        ['12345678512348', false],
        ['12345678612347', false],
        ['1234567', false],
    ]);
});

it('validates KRS numbers', function (): void {
    expect(Krs::isValid('0000123456'))->toBeTrue()
        ->and(Krs::isValid('123456'))->toBeFalse()
        ->and(Krs::isValid('0000000000'))->toBeFalse();
});

describe('IBAN', function (): void {
    it('accepts valid accounts', function (string $iban): void {
        expect(Iban::isValid($iban))->toBeTrue();
    })->with([
        'PL61109010140000071219812874',
        'PL61 1090 1014 0000 0712 1981 2874',
        '61109010140000071219812874',
        'DE89370400440532013000',
        'GB82WEST12345698765432',
    ]);

    it('rejects invalid accounts', function (string $iban): void {
        expect(Iban::isValid($iban))->toBeFalse();
    })->with([
        'PL61109010140000071219812875',
        'PL6110901014000007121981287',
        'XX61109010140000071219812874',
        'DE8937040044053201300',
    ]);

    it('restricts countries and the NRB form', function (): void {
        expect(Iban::isValid('DE89370400440532013000', ['PL']))->toBeFalse()
            ->and(Iban::isValid('PL61109010140000071219812874', ['pl']))->toBeTrue()
            ->and(Iban::isValid('61109010140000071219812874', allowNrb: false))->toBeFalse();
    });

    it('formats accounts', function (): void {
        expect(Iban::format('61109010140000071219812874'))->toBe('PL61 1090 1014 0000 0712 1981 2874');
    });
});

it('validates identity cards', function (string $number, bool $valid): void {
    expect(IdCard::isValid($number))->toBe($valid);
})->with([
    ['ABA300000', true],
    ['aba 300000', true],
    ['ABA400000', false],
    ['AB1300000', false],
]);

it('validates postal codes', function (): void {
    expect(PostalCode::isValid('00-950'))->toBeTrue()
        ->and(PostalCode::isValid('00950'))->toBeFalse()
        ->and(PostalCode::isValid('00950', strict: false))->toBeTrue()
        ->and(PostalCode::normalize('00 950'))->toBe('00-950')
        ->and(PostalCode::normalize('123'))->toBeNull();
});

it('validates and normalizes phone numbers', function (string $phone, ?string $national): void {
    expect(PhoneNumber::normalize($phone))->toBe($national)
        ->and(PhoneNumber::isValid($phone))->toBe($national !== null);
})->with([
    ['500100200', '500100200'],
    ['500 100 200', '500100200'],
    ['+48 500-100-200', '500100200'],
    ['0048500100200', '500100200'],
    ['48500100200', '500100200'],
    ['+49500100200', null],
    ['050010020', null],
    ['50010020', null],
    ['phone: 500100200', null],
]);

it('converts phone numbers to E.164', function (): void {
    expect(PhoneNumber::toE164('500 100 200'))->toBe('+48500100200')
        ->and(PhoneNumber::toE164('1'))->toBeNull();
});
