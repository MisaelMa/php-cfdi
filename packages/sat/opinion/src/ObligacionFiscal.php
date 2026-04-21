<?php

declare(strict_types=1);

namespace Sat\Opinion;

final readonly class ObligacionFiscal
{
    public function __construct(
        public string $descripcion,
        public string $fechaInicio,
        public ?string $fechaFin = null,
        public string $estado = 'Activa',
    ) {
    }
}
