<?php

declare(strict_types=1);

namespace Sat\Pacs;

/** Solicitud de timbrado: CFDI en XML (cadena). */
final readonly class TimbradoRequest
{
    public function __construct(
        public string $xml,
    ) {
    }
}
