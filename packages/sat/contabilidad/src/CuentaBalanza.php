<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

readonly final class CuentaBalanza
{
    public function __construct(
        public string $numCta,
        public float $saldoIni,
        public float $debe,
        public float $haber,
        public float $saldoFin,
    ) {
    }
}
