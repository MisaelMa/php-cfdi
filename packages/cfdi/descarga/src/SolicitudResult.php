<?php

namespace Cfdi\Descarga;

readonly class SolicitudResult
{
    public function __construct(
        public string $idSolicitud,
        public string $codEstatus,
        public string $mensaje,
    ) {
    }
}
