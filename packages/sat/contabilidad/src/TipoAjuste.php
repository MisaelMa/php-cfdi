<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

enum TipoAjuste: string
{
    case Cierre = 'C';
    case Apertura = 'A';
}
