<?php

namespace Cfdi\Validador;

class CfdiData
{
    public function __construct(
        public readonly string $version,
        public readonly array $comprobante,
        public readonly array $emisor,
        public readonly array $receptor,
        public readonly array $conceptos,
        public readonly ?array $impuestos,
        public readonly ?array $timbre,
        public readonly string $raw,
    ) {
    }
}
