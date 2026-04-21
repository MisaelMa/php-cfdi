<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

readonly final class CuentaAuxiliar
{
    /**
     * @param list<TransaccionAuxiliar> $transacciones
     */
    public function __construct(
        public string $numCta,
        public string $desCta,
        public float $saldoIni,
        public float $saldoFin,
        public array $transacciones,
    ) {
    }
}
