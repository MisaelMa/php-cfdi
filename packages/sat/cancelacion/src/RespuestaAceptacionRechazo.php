<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

enum RespuestaAceptacionRechazo: string
{
    case Aceptacion = 'Aceptacion';
    case Rechazo = 'Rechazo';
}
