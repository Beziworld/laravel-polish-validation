<?php

declare(strict_types=1);

namespace Beziworld\PolishValidation\Enums;

/**
 * Gender encoded in the tenth digit of a PESEL number.
 */
enum Gender: string
{
    case Female = 'female';
    case Male = 'male';
}
