<?php

namespace Cfdi\Retenciones;

final readonly class ReceptorNacional
{
    public function __construct(
        public string $RfcRecep,
        public ?string $NomDenRazSocR = null,
        public ?string $CurpR = null,
    ) {
    }
}
