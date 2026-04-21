<?php

namespace Cfdi\Descarga;

readonly class VerificacionResult
{
    /**
     * @param list<string> $idsPaquetes
     */
    public function __construct(
        public EstadoSolicitud $estado,
        public string $estadoDescripcion,
        public string $codEstatus,
        public string $mensaje,
        public array $idsPaquetes,
        public int $numeroCfdis,
    ) {
    }
}
