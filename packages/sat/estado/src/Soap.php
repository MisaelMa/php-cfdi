<?php

namespace Cfdi\Estado;

class Soap
{
    public const WEBSERVICE_URL = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc';
    public const SOAP_ACTION = 'http://tempuri.org/IConsultaCFDIService/Consulta';

    /**
     * Formatea el total del CFDI al formato requerido por el SAT:
     * 17 caracteres totales, 6 decimales, relleno con ceros a la izquierda.
     */
    public static function formatTotal(string $total): string
    {
        if (!is_numeric($total)) {
            throw new \InvalidArgumentException("Total invalido: '{$total}'");
        }

        $parts = explode('.', $total);
        $integer = $parts[0];
        $decimal = $parts[1] ?? '';

        $decimal = str_pad(substr($decimal, 0, 6), 6, '0');
        $paddedInteger = str_pad($integer, 10, '0', STR_PAD_LEFT);

        return "{$paddedInteger}.{$decimal}";
    }

    public static function buildSoapRequest(ConsultaParams $params): string
    {
        $totalFormateado = self::formatTotal($params->total);
        $expresion = "?re={$params->rfcEmisor}&rr={$params->rfcReceptor}&tt={$totalFormateado}&id={$params->uuid}";

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
  <soap:Header/>
  <soap:Body>
    <tem:Consulta>
      <tem:expresionImpresa><![CDATA[{$expresion}]]></tem:expresionImpresa>
    </tem:Consulta>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private static function extractTag(string $xml, string $localName): string
    {
        $pattern = '/<(?:[a-zA-Z0-9_]+:)?' . preg_quote($localName, '/') . '[^>]*>([\s\S]*?)<\/(?:[a-zA-Z0-9_]+:)?' . preg_quote($localName, '/') . '>/i';
        if (preg_match($pattern, $xml, $match)) {
            return trim($match[1]);
        }
        return '';
    }

    public static function parseSoapResponse(string $xml): ConsultaResult
    {
        if (str_contains($xml, '<s:Fault>') || str_contains($xml, '<soap:Fault>')) {
            $faultString = self::extractTag($xml, 'faultstring');
            throw new \RuntimeException('SOAP Fault: ' . ($faultString ?: 'Error desconocido del servicio'));
        }

        return new ConsultaResult(
            codigoEstatus: self::extractTag($xml, 'CodigoEstatus'),
            esCancelable: self::extractTag($xml, 'EsCancelable'),
            estado: self::extractTag($xml, 'Estado'),
            estatusCancelacion: self::extractTag($xml, 'EstatusCancelacion'),
            validacionEFOS: self::extractTag($xml, 'ValidacionEFOS'),
        );
    }
}
