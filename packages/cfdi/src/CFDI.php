<?php


namespace Sat;

use Sat\Cfdi\Comprobante;
use Spatie\ArrayToXml\ArrayToXml;

class CFDI extends Comprobante
{
    private function formatXML($xml)
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml);
        return $dom->saveXML();
    }

    public function getXmlCdfi(): string
    {
        var_dump($this->xml);
        $xml_string = ArrayToXml::convert($this->xml, 'cfdi:Comprobante');
        $xml = $this->formatXML($xml_string);
        var_dump($xml);

        return $xml;
    }
}
