<?php

namespace Cfdi\Retenciones;

final readonly class ComplementoRetencion
{
    /**
     * @param array<string, mixed>|null $meta
     */
    public function __construct(
        public string $innerXml,
        public ?array $meta = null,
    ) {
    }
}
