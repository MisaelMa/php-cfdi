<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

enum EstatusCancelacion: string
{
    case EnProceso = 'EnProceso';
    case Cancelado = 'Cancelado';
    case CancelacionRechazada = 'Rechazada';
    case Plazo = 'Plazo';
}
