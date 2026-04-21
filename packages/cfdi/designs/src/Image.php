<?php

declare(strict_types=1);

namespace Cfdi\Designs;

class Image extends PdfElement
{
    public function __construct(
        private string $pathOrUrl,
        private ?int $width = null,
        private ?int $height = null,
    ) {
    }

    public function toArray(): array
    {
        $img = ['image' => $this->pathOrUrl];
        if ($this->width !== null) {
            $img['width'] = $this->width;
        }
        if ($this->height !== null) {
            $img['height'] = $this->height;
        }

        return $img;
    }
}
