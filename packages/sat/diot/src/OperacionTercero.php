<?php

declare(strict_types=1);

namespace Sat\Diot;

final readonly class OperacionTercero
{
    public function __construct(
        public TipoTercero $tipoTercero,
        public TipoOperacion $tipoOperacion,
        public float $montoIva16,
        public float $montoIva0,
        public float $montoExento,
        public float $montoRetenido,
        public float $montoIvaNoDeduc,
        public ?string $rfc = null,
        /** Obligatorio para proveedor extranjero; vacío u omitido en nacional. */
        public ?string $idFiscal = null,
        public ?string $nombreExtranjero = null,
        public ?string $paisResidencia = null,
        public ?string $nacionalidad = null,
    ) {
    }
}
