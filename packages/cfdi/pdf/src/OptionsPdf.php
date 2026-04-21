<?php

namespace Cfdi\Pdf;

class OptionsPdf
{
    public function __construct(
        public readonly Logo|string|null $logo = null,
        public readonly ?string $lugarExpedicion = null,
        public readonly ?array $fonts = null,
    ) {}
}
