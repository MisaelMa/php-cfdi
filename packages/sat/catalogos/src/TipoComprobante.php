<?php

namespace Cfdi\Catalogos;

enum TipoComprobante: string
{
    case INGRESO = 'I';
    case EGRESO = 'E';
    case TRASLADO = 'T';
    case PAGO = 'P';
    case NOMINA = 'N';

    public function label(): string
    {
        return match ($this) {
            self::INGRESO => 'Ingreso',
            self::EGRESO => 'Egreso',
            self::TRASLADO => 'Translado',
            self::PAGO => 'Pago',
            self::NOMINA => 'Nómina',
        };
    }
}
