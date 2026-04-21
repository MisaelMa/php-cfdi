<?php

namespace Cfdi\Retenciones;

final readonly class ReceptorRetencion
{
    public function __construct(
        public NacionalidadReceptor $NacionalidadR,
        public ?ReceptorNacional $nacional = null,
        public ?ReceptorExtranjero $extranjero = null,
    ) {
    }
}
