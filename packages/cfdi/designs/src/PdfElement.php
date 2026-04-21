<?php

declare(strict_types=1);

namespace Cfdi\Designs;

abstract class PdfElement
{
    abstract public function toArray(): array;
}
