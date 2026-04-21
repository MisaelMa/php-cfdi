<?php

declare(strict_types=1);

namespace Cfdi\Designs;

class Pdf
{
    /** @var list<PdfElement|array> */
    private array $content = [];

    /** @var array<string, array> */
    private array $styles = [];

    private ?array $defaultStyle = null;

    public function add(PdfElement|array $block): self
    {
        $this->content[] = $block;

        return $this;
    }

    public function defineStyle(string $name, Style $style): self
    {
        $this->styles[$name] = $style->getDefinition();

        return $this;
    }

    public function setDefaultStyle(array $definition): self
    {
        $this->defaultStyle = $definition;

        return $this;
    }

    public function toArray(): array
    {
        $doc = [
            'content' => array_map(
                function (PdfElement|array $item) {
                    if ($item instanceof PdfElement) {
                        return $item->toArray();
                    }

                    return $item;
                },
                $this->content
            ),
        ];
        if ($this->styles !== []) {
            $doc['styles'] = $this->styles;
        }
        if ($this->defaultStyle !== null) {
            $doc['defaultStyle'] = $this->defaultStyle;
        }

        return $doc;
    }
}
