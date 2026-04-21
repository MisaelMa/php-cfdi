<?php

namespace Cfdi\Catalogos;

enum Impuesto: string
{
    case ISR = '001';
    case IVA = '002';
    case IEPS = '003';

    public function label(): string
    {
        return match ($this) {
            self::ISR => 'ISR',
            self::IVA => 'IVA',
            self::IEPS => 'IEPS',
        };
    }
}
