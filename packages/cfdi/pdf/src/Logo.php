<?php

namespace Cfdi\Pdf;

class Logo
{
    public function __construct(
        public readonly ?float $width = null,
        public readonly ?float $height = null,
        public readonly ?string $image = null,
    ) {}
}
