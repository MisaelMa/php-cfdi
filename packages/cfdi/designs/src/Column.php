<?php

declare(strict_types=1);

namespace Cfdi\Designs;

class Column extends PdfElement
{
    /** @var list<PdfElement> */
    private array $stack = [];

    public function __construct(private ?string $width = null)
    {
    }

    public function push(PdfElement $element): self
    {
        $this->stack[] = $element;

        return $this;
    }

    public function toArray(): array
    {
        $col = [
            'stack' => array_map(static fn (PdfElement $e) => $e->toArray(), $this->stack),
        ];
        if ($this->width !== null) {
            $col['width'] = $this->width;
        }

        return $col;
    }
}
