<?php

namespace Cfdi\Validador;

class Parser
{
    public static function parse(string $xml): CfdiData
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING);

        $comprobante = self::findElement($doc->documentElement ? [$doc->documentElement] : [], 'Comprobante');
        if ($comprobante === null) {
            throw new \RuntimeException('XML no contiene el elemento Comprobante en el namespace cfdi');
        }

        $comprobanteAttrs = self::getAttributes($comprobante);
        $version = $comprobanteAttrs['Version'] ?? '';

        $emisorEl = self::findChildElement($comprobante, 'Emisor');
        $receptorEl = self::findChildElement($comprobante, 'Receptor');
        $conceptosEl = self::findChildElement($comprobante, 'Conceptos');
        $impuestosEl = self::findChildElement($comprobante, 'Impuestos');
        $complementoEl = self::findChildElement($comprobante, 'Complemento');

        $conceptos = [];
        if ($conceptosEl !== null) {
            foreach (self::findChildElements($conceptosEl, 'Concepto') as $conceptoEl) {
                $conceptos[] = self::parseConcepto($conceptoEl);
            }
        }

        return new CfdiData(
            version: $version,
            comprobante: $comprobanteAttrs,
            emisor: $emisorEl ? self::getAttributes($emisorEl) : [],
            receptor: $receptorEl ? self::getAttributes($receptorEl) : [],
            conceptos: $conceptos,
            impuestos: self::parseImpuestos($impuestosEl),
            timbre: self::parseTimbre($complementoEl),
            raw: $xml,
        );
    }

    private static function stripNs(string $name): string
    {
        $pos = strpos($name, ':');
        return $pos !== false ? substr($name, $pos + 1) : $name;
    }

    private static function getAttributes(\DOMElement $element): array
    {
        $attrs = [];
        foreach ($element->attributes as $attr) {
            $attrs[self::stripNs($attr->nodeName)] = $attr->value;
        }
        return $attrs;
    }

    private static function findElement(array $elements, string $localName): ?\DOMElement
    {
        foreach ($elements as $el) {
            if ($el instanceof \DOMElement && self::stripNs($el->nodeName) === $localName) {
                return $el;
            }
        }
        return null;
    }

    private static function findChildElement(\DOMElement $parent, string $localName): ?\DOMElement
    {
        foreach ($parent->childNodes as $child) {
            if ($child instanceof \DOMElement && self::stripNs($child->nodeName) === $localName) {
                return $child;
            }
        }
        return null;
    }

    private static function findChildElements(\DOMElement $parent, string $localName): array
    {
        $result = [];
        foreach ($parent->childNodes as $child) {
            if ($child instanceof \DOMElement && self::stripNs($child->nodeName) === $localName) {
                $result[] = $child;
            }
        }
        return $result;
    }

    private static function parseConcepto(\DOMElement $conceptoEl): array
    {
        $attributes = self::getAttributes($conceptoEl);
        $impuestosEl = self::findChildElement($conceptoEl, 'Impuestos');

        if ($impuestosEl === null) {
            return ['attributes' => $attributes];
        }

        return [
            'attributes' => $attributes,
            'impuestos' => [
                'traslados' => self::parseTraslados($impuestosEl),
                'retenciones' => self::parseRetenciones($impuestosEl),
            ],
        ];
    }

    private static function parseTraslados(\DOMElement $impuestosEl): array
    {
        $trasladosEl = self::findChildElement($impuestosEl, 'Traslados');
        if ($trasladosEl === null) {
            return [];
        }
        $result = [];
        foreach (self::findChildElements($trasladosEl, 'Traslado') as $el) {
            $result[] = self::getAttributes($el);
        }
        return $result;
    }

    private static function parseRetenciones(\DOMElement $impuestosEl): array
    {
        $retencionesEl = self::findChildElement($impuestosEl, 'Retenciones');
        if ($retencionesEl === null) {
            return [];
        }
        $result = [];
        foreach (self::findChildElements($retencionesEl, 'Retencion') as $el) {
            $result[] = self::getAttributes($el);
        }
        return $result;
    }

    private static function parseImpuestos(?\DOMElement $impuestosEl): ?array
    {
        if ($impuestosEl === null) {
            return null;
        }
        $attrs = self::getAttributes($impuestosEl);
        return [
            'totalImpuestosTrasladados' => $attrs['TotalImpuestosTrasladados'] ?? null,
            'totalImpuestosRetenidos' => $attrs['TotalImpuestosRetenidos'] ?? null,
            'traslados' => self::parseTraslados($impuestosEl),
            'retenciones' => self::parseRetenciones($impuestosEl),
        ];
    }

    private static function parseTimbre(?\DOMElement $complementoEl): ?array
    {
        if ($complementoEl === null) {
            return null;
        }
        $tfdEl = self::findChildElement($complementoEl, 'TimbreFiscalDigital');
        if ($tfdEl === null) {
            return null;
        }
        $attrs = self::getAttributes($tfdEl);
        return [
            'uuid' => $attrs['UUID'] ?? '',
            'fechaTimbrado' => $attrs['FechaTimbrado'] ?? '',
            'rfcProvCertif' => $attrs['RfcProvCertif'] ?? '',
            'selloCFD' => $attrs['SelloCFD'] ?? '',
            'selloSAT' => $attrs['SelloSAT'] ?? '',
            'noCertificadoSAT' => $attrs['NoCertificadoSAT'] ?? '',
            'version' => $attrs['Version'] ?? '',
        ];
    }
}
