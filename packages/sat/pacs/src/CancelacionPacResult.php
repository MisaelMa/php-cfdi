<?php

declare(strict_types=1);

namespace Sat\Pacs;

/** Resultado de una solicitud de cancelación ante el PAC. */
final readonly class CancelacionPacResult
{
    public function __construct(
        public string $uuid,
        public string $estatus,
        public string $acuse,
    ) {
    }
}
