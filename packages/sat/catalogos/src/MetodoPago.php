<?php

namespace Cfdi\Catalogos;

enum MetodoPago: string
{
    case PAGO_EN_UNA_EXHIBICION = 'PUE';
    case PAGO_EN_PARCIALIDADES_DIFERIDO = 'PPD';

    public function label(): string
    {
        return match ($this) {
            self::PAGO_EN_UNA_EXHIBICION => 'Pago en una sola exhibición',
            self::PAGO_EN_PARCIALIDADES_DIFERIDO => 'Pago en parcialidades o diferido',
        };
    }
}
