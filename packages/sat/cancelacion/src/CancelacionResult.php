<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

readonly final class CancelacionResult
{
    public function __construct(
        public string $uuid,
        public EstatusCancelacion $estatus,
        public string $codEstatus,
        public string $mensaje,
    ) {
    }
}
