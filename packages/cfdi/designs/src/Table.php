<?php

declare(strict_types=1);

namespace Cfdi\Designs;

class Table extends PdfElement
{
    /** @var list<Row> */
    private array $rows = [];

    /**
     * @param list<int|string>|null $widths
     */
    public function __construct(private ?array $widths = null)
    {
    }

    public function addRow(Row $row): self
    {
        $this->rows[] = $row;

        return $this;
    }

    public function toArray(): array
    {
        $table = [
            'body' => array_map(
                static fn (Row $r) => $r->toArray(),
                $this->rows
            ),
        ];
        if ($this->widths !== null) {
            $table['widths'] = $this->widths;
        }

        return ['table' => $table];
    }
}
