<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

readonly final class PolizaDetalle
{
    public function __construct(
        public string $numUnidad,
        public string $concepto,
        public float $debe,
        public float $haber,
        public string $numCta,
    ) {
    }
}
