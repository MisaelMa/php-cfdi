<?php

declare(strict_types=1);

namespace Sat\Diot;

enum TipoOperacion: string
{
    case ProfesionalesHonorarios = '85';
    case Arrendamiento = '06';
    case OtrosConIVA = '03';
    case OtrosSinIVA = '04';
}
