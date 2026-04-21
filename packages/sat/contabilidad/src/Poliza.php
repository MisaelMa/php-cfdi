<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

readonly final class Poliza
{
    /**
     * @param list<PolizaDetalle> $detalle
     */
    public function __construct(
        public string $numPoliza,
        public string $fecha,
        public string $concepto,
        public array $detalle,
    ) {
    }
}
