<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

enum NaturalezaCuenta: string
{
    case Deudora = 'D';
    case Acreedora = 'A';
}
