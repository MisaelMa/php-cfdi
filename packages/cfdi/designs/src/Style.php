<?php

declare(strict_types=1);

namespace Cfdi\Designs;

class Style extends PdfElement
{
    public function __construct(private array $definition)
    {
    }

    public function toArray(): array
    {
        return $this->definition;
    }

    public function getDefinition(): array
    {
        return $this->definition;
    }
}
