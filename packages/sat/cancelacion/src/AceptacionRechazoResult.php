<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

readonly final class AceptacionRechazoResult
{
    public function __construct(
        public string $uuid,
        public string $codEstatus,
        public string $mensaje,
    ) {
    }
}
