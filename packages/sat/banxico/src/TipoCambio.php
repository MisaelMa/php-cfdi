<?php

declare(strict_types=1);

namespace Sat\Banxico;

final readonly class TipoCambio
{
    public function __construct(
        public string $fecha,
        public float $valor,
        public Moneda $moneda,
    ) {
    }
}
