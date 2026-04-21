<?php

declare(strict_types=1);

namespace Cfdi\Designs;

class Text extends PdfElement
{
    public function __construct(
        private string $text,
        private ?string $style = null,
    ) {
    }

    public function toArray(): array
    {
        $row = ['text' => $this->text];
        if ($this->style !== null) {
            $row['style'] = $this->style;
        }

        return $row;
    }
}
