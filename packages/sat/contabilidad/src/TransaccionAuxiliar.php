<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

readonly final class TransaccionAuxiliar
{
    public function __construct(
        public string $fecha,
        public string $numPoliza,
        public string $concepto,
        public float $debe,
        public float $haber,
    ) {
    }
}
