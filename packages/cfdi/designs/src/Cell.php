<?php

declare(strict_types=1);

namespace Cfdi\Designs;

class Cell extends PdfElement
{
    public function __construct(private PdfElement|array|string $content)
    {
    }

    public function toArray(): array
    {
        if ($this->content instanceof PdfElement) {
            return $this->content->toArray();
        }
        if (is_string($this->content)) {
            return ['text' => $this->content];
        }

        return $this->content;
    }
}
