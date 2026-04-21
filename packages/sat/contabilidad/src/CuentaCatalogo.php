<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

readonly final class CuentaCatalogo
{
    public function __construct(
        public string $codAgrup,
        public string $numCta,
        public string $desc,
        public int $nivel,
        public NaturalezaCuenta $natur,
        public ?string $subCtaDe = null,
    ) {
    }
}
