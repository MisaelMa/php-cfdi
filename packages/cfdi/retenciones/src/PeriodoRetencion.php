<?php

namespace Cfdi\Retenciones;

final readonly class PeriodoRetencion
{
    public function __construct(
        public string $MesIni,
        public string $MesFin,
        public string $Ejerc,
    ) {
    }
}
