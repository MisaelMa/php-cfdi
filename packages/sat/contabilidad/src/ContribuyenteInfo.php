<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

readonly final class ContribuyenteInfo
{
    public function __construct(
        public string $rfc,
        public string $mes,
        public int $anio,
        public TipoEnvio $tipoEnvio,
    ) {
    }
}
