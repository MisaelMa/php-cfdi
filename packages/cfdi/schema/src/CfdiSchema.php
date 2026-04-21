<?php

declare(strict_types=1);

namespace Cfdi\Schema;

class CfdiSchema
{
    private string $cfdiSource = '';

    private string $catalogosSource = '';

    /**
     * @param array{cfdi?: string, catalogos?: string} $config
     */
    public function setConfig(array $config): self
    {
        $this->cfdiSource = (string) ($config['cfdi'] ?? '');
        $this->catalogosSource = (string) ($config['catalogos'] ?? '');

        return $this;
    }

    /**
     * @return array{cfdi: array{comprobanteSequence: list<array{name: string, type: ?string, minOccurs: ?string, maxOccurs: ?string}>, jsonLike: array<string, mixed>}, catalogos?: array{comprobanteSequence: list<array{name: string, type: ?string, minOccurs: ?string, maxOccurs: ?string}>, jsonLike: array<string, mixed>}}
     */
    public function processAll(): array
    {
        if ($this->cfdiSource === '') {
            throw new \RuntimeException('cfdi XSD path is required');
        }

        $cfdi = CfdiXsd::of();
        $cfdi->setConfig(['source' => $this->cfdiSource]);
        $cfdiPart = $cfdi->process();

        $out = ['cfdi' => $cfdiPart];

        if ($this->catalogosSource !== '') {
            $cat = new CfdiXsd();
            $cat->setConfig(['source' => $this->catalogosSource]);
            $out['catalogos'] = $cat->process();
        }

        return $out;
    }
}
