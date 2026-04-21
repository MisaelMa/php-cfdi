<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

enum TipoSolicitud: string
{
    case AF = 'AF';
    case FC = 'FC';
    case DE = 'DE';
    case CO = 'CO';
}
