<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

enum TipoEnvio: string
{
    case Normal = 'N';
    case Complementaria = 'C';
}
