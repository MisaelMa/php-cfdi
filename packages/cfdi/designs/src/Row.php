<?php

declare(strict_types=1);

namespace Cfdi\Designs;

class Row extends PdfElement
{
    /** @var list<Column|Cell|PdfElement> */
    private array $columns = [];

    public function addColumn(Column|Cell|PdfElement $part): self
    {
        $this->columns[] = $part;

        return $this;
    }

    public function toArray(): array
    {
        return array_map(
            static fn (PdfElement $p) => $p->toArray(),
            $this->columns
        );
    }
}
