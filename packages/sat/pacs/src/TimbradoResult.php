<?php

declare(strict_types=1);

namespace Sat\Pacs;

/** Respuesta de timbrado exitosa con datos del Timbre Fiscal Digital. */
final readonly class TimbradoResult
{
    public function __construct(
        public string $uuid,
        public string $fecha,
        public string $selloCFD,
        public string $selloSAT,
        public string $noCertificadoSAT,
        public string $cadenaOriginalSAT,
        public string $xml,
    ) {
    }
}
