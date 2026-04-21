<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

readonly final class PendientesResult
{
    public function __construct(
        public string $uuid,
        public string $rfcEmisor,
        public string $fechaSolicitud,
    ) {
    }
}
