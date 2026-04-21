<?php

namespace Cfdi\Descarga;

readonly class SolicitudParams
{
    public function __construct(
        public string $rfcSolicitante,
        public string $fechaInicio,
        public string $fechaFin,
        public TipoSolicitud $tipoSolicitud,
        public TipoDescarga $tipoDescarga,
        public ?string $rfcEmisor = null,
        public ?string $rfcReceptor = null,
    ) {
    }
}
