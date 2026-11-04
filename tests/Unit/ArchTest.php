<?php

declare(strict_types=1);

arch('every source file declares strict types')
    ->expect('Beziworld\PolishValidation')
    ->toUseStrictTypes();

arch('no debugging statements are left behind')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r', 'die'])
    ->not->toBeUsed();

arch('data objects are immutable')
    ->expect('Beziworld\PolishValidation\Data')
    ->toBeReadonly();

arch('exceptions share the package base exception')
    ->expect('Beziworld\PolishValidation\Exceptions')
    ->toExtend('Beziworld\PolishValidation\Exceptions\PolishValidationException')
    ->ignoring('Beziworld\PolishValidation\Exceptions\PolishValidationException');
