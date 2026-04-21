<?php

namespace Cfdi\Descarga;

enum EstadoSolicitud: int
{
    case Aceptada = 1;
    case EnProceso = 2;
    case Terminada = 3;
    case Error = 4;
    case Rechazada = 5;
    case Vencida = 6;

    public function label(): string
    {
        return match ($this) {
            self::Aceptada => 'Aceptada',
            self::EnProceso => 'En proceso',
            self::Terminada => 'Terminada',
            self::Error => 'Error',
            self::Rechazada => 'Rechazada',
            self::Vencida => 'Vencida',
        };
    }
}
