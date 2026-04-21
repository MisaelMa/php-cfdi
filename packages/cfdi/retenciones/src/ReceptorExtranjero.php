<?php

namespace Cfdi\Retenciones;

final readonly class ReceptorExtranjero
{
    public function __construct(
        public string $NomDenRazSocR,
        public ?string $NumRegIdTrib = null,
    ) {
    }
}
