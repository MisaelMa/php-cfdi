<?php

declare(strict_types=1);

namespace Sat\Pacs;

/** Resultado de consultar el estatus de un UUID (p. ej. pendiente de timbrado o estado SAT). */
final readonly class ConsultaEstatusResult
{
    public function __construct(
        public string $uuid,
        public string $estatus,
        public ?string $xml = null,
    ) {
    }
}
