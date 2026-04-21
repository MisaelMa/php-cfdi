<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

readonly final class AceptacionRechazoParams
{
    public function __construct(
        public string $rfcReceptor,
        public string $uuid,
        public RespuestaAceptacionRechazo $respuesta,
    ) {
    }
}
