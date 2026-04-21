<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

readonly final class CancelacionParams
{
    public function __construct(
        public string $uuid,
        public MotivoCancelacion $motivo,
        public ?string $rfcEmisor = null,
        /** UUID del CFDI que sustituye (requerido cuando motivo = ConRelacion) */
        public ?string $folioSustitucion = null,
    ) {
    }
}
