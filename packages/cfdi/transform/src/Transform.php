<?php

namespace Cfdi\Transform;

class Transform
{
    private string $xmlContent = '';
    private ?TemplateRegistry $registry = null;

    public function s(string $archivo): static
    {
        $this->xmlContent = file_get_contents($archivo);
        return $this;
    }

    public function xsl(string $xslPath): static
    {
        $this->registry = XsltParser::parse($xslPath);
        return $this;
    }

    public function json(string $xslPath): static
    {
        return $this->xsl($xslPath);
    }

    public function warnings(string $type = 'silent'): static
    {
        return $this;
    }

    public function run(): string
    {
        if ($this->registry === null) {
            throw new \RuntimeException('XSLT not loaded. Call xsl() or json() first.');
        }
        return CadenaEngine::generateCadenaOriginal($this->xmlContent, $this->registry);
    }
}
