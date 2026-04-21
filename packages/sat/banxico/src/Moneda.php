<?php

declare(strict_types=1);

namespace Sat\Banxico;

enum Moneda: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case JPY = 'JPY';
    case CAD = 'CAD';
}
