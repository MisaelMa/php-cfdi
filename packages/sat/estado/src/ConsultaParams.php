<?php

namespace Cfdi\Estado;

class ConsultaParams
{
    public function __construct(
        public readonly string $rfcEmisor,
        public readonly string $rfcReceptor,
        public readonly string $total,
        public readonly string $uuid,
    ) {
    }
}
