<?php

declare(strict_types=1);

namespace Sat;

use Cfdi\Transform\Transform;
use Sat\Cfdi\Comprobante;
use Sat\Csd\Certificate;
use Sat\Csd\PrivateKey;
use Spatie\ArrayToXml\ArrayToXml;

class CFDI extends Comprobante
{
    private string $cadenaOriginal = '';

    private string $sello = '';

    private ?string $xsltPath = null;

    /**
     * @param array<string, mixed> $options
     *        'xslt' => string (path) or array{ path: string } — cadena original XSLT (Node: Config.xslt)
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $xslt = $options['xslt'] ?? null;
        if (is_string($xslt)) {
            $this->xsltPath = $xslt;
        } elseif (is_array($xslt) && isset($xslt['path'])) {
            $this->xsltPath = (string) $xslt['path'];
        }
    }

    /**
     * Load .cer, set NoCertificado and Certificado on the comprobante (Node: certificar).
     */
    public function certificar(string $cerPath): self
    {
        $cert = Certificate::fromFile($cerPath);
        $this->setNoCertificado($cert->noCertificado());
        $this->setCertificado(base64_encode($cert->toDer()));

        return $this;
    }

    /**
     * Build cadena original from current CFDI XML and store it internally.
     */
    public function generarCadenaOriginal(): string
    {
        if ($this->xsltPath === null || $this->xsltPath === '') {
            throw new \RuntimeException(
                '¡Ups! Direcction Not Found Extensible Stylesheet Language Transformation'
            );
        }

        $xml = $this->buildXmlString();
        $tmp = tempnam(sys_get_temp_dir(), 'cfdi_cadena_');
        if ($tmp === false) {
            throw new \RuntimeException('No se pudo crear archivo temporal para la cadena original');
        }

        try {
            if (file_put_contents($tmp, $xml) === false) {
                throw new \RuntimeException('No se pudo escribir XML temporal');
            }

            $transform = new Transform();
            $cadena = $transform->s($tmp)->xsl($this->xsltPath)->warnings('silent')->run();
            $this->cadenaOriginal = $cadena;

            return $cadena;
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * Sign cadena original with the private key (Node: generarSello).
     */
    public function generarSello(string $cadenaOriginal, string $keyPath, string $password): string
    {
        $pk = PrivateKey::fromFile($keyPath, $password);

        return $pk->sign($cadenaOriginal);
    }

    /**
     * Generate cadena original, sign it, and set Sello (Node: sellar).
     */
    public function sellar(string $keyPath, string $password): self
    {
        $cadena = $this->generarCadenaOriginal();
        $sello = $this->generarSello($cadena, $keyPath, $password);
        $this->cadenaOriginal = $cadena;
        $this->setSello($sello);

        return $this;
    }

    /**
     * Internal CFDI structure as array (Node: getJsonCdfi → xml).
     *
     * @return array<string, mixed>
     */
    public function getJsonCdfi(): array
    {
        return $this->xml;
    }

    public function getCadenaOriginal(): string
    {
        return $this->cadenaOriginal;
    }

    public function getSello(): string
    {
        $fromXml = (string) ($this->xml['_attributes']['Sello'] ?? '');

        return $fromXml !== '' ? $fromXml : $this->sello;
    }

    public function getXmlCdfi(): string
    {
        return $this->buildXmlString();
    }

    protected function buildXmlString(): string
    {
        $xml_string = ArrayToXml::convert($this->xml, 'cfdi:Comprobante');

        return $this->formatXML($xml_string);
    }

    private function formatXML($xml)
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml);

        return $dom->saveXML();
    }
}
