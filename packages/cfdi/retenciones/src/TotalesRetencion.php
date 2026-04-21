<?php

namespace Cfdi\Retenciones;

final readonly class TotalesRetencion
{
    public function __construct(
        public string $montoTotOperacion,
        public string $montoTotGrav,
        public string $montoTotExent,
        public string $montoTotRet,
    ) {
    }
}
