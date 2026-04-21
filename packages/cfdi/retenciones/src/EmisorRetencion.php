<?php

namespace Cfdi\Retenciones;

final readonly class EmisorRetencion
{
    public function __construct(
        public string $Rfc,
        public string $RegimenFiscalE,
        public ?string $NomDenRazSocE = null,
        public ?string $CurpE = null,
    ) {
    }
}
