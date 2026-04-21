<?php

namespace Cfdi\Descarga\Soap;

use Cfdi\Descarga\EstadoSolicitud;
use Cfdi\Descarga\VerificacionResult;
use RuntimeException;

final class Verificar
{
    public static function buildVerificarRequest(
        string $idSolicitud,
        string $rfc,
        string $token,
        string $cert,
        string $signatureValue,
    ): string {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx/"
            xmlns:xd="http://www.w3.org/2000/09/xmldsig#">
  <s:Header>
    <h:Security xmlns:h="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
      <u:Timestamp>
        <u:Created>{$token}</u:Created>
      </u:Timestamp>
      <xd:Signature>
        <xd:SignedInfo>
          <xd:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
          <xd:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
          <xd:Reference URI="#_0">
            <xd:Transforms>
              <xd:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </xd:Transforms>
            <xd:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
            <xd:DigestValue></xd:DigestValue>
          </xd:Reference>
        </xd:SignedInfo>
        <xd:SignatureValue>{$signatureValue}</xd:SignatureValue>
        <xd:KeyInfo>
          <xd:X509Data>
            <xd:X509Certificate>{$cert}</xd:X509Certificate>
          </xd:X509Data>
        </xd:KeyInfo>
      </xd:Signature>
    </h:Security>
  </s:Header>
  <s:Body>
    <des:VerificaSolicitudDescarga>
      <des:solicitud IdSolicitud="{$idSolicitud}"
                     RfcSolicitante="{$rfc}">
        <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                      Id="SelloDigital">
          <ds:SignedInfo>
            <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
            <ds:Reference URI="">
              <ds:Transforms>
                <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
              </ds:Transforms>
              <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
              <ds:DigestValue></ds:DigestValue>
            </ds:Reference>
          </ds:SignedInfo>
          <ds:SignatureValue>{$signatureValue}</ds:SignatureValue>
          <ds:KeyInfo>
            <ds:X509Data>
              <ds:X509Certificate>{$cert}</ds:X509Certificate>
            </ds:X509Data>
          </ds:KeyInfo>
        </ds:Signature>
      </des:solicitud>
    </des:VerificaSolicitudDescarga>
  </s:Body>
</s:Envelope>
XML;
    }

    public static function parseVerificarResponse(string $xml): VerificacionResult
    {
        if (str_contains($xml, '<faultcode>') || str_contains($xml, ':Fault>')) {
            $faultString = self::extractTag($xml, 'faultstring');
            throw new RuntimeException(
                'SOAP Fault: ' . ($faultString !== '' ? $faultString : 'Error desconocido del servicio')
            );
        }

        $openingTag = self::extractOpeningTag($xml, 'VerificaSolicitudDescargaResult')
            ?: self::extractOpeningTag($xml, 'RespuestaVerificaSolicitudDescMasivaTercerosSolicitud');

        $ctx = $openingTag !== '' ? $openingTag : $xml;

        $codEstatus = self::extractAttr($ctx, 'CodEstatus');
        $mensaje = self::extractAttr($ctx, 'Mensaje');
        $estadoRaw = self::extractAttr($ctx, 'EstadoSolicitud');
        $numeroCfdisRaw = self::extractAttr($ctx, 'NumeroCFDIs');

        $idsPaquetes = self::extractAllTags($xml, 'IdsPaquetes');

        $estadoNum = (int) $estadoRaw;
        $estado = EstadoSolicitud::tryFrom($estadoNum) ?? EstadoSolicitud::Error;
        $estadoDescripcion = $estado->label();

        return new VerificacionResult(
            $estado,
            $estadoDescripcion,
            $codEstatus,
            $mensaje,
            $idsPaquetes,
            (int) $numeroCfdisRaw,
        );
    }

    private static function extractTag(string $xml, string $localName): string
    {
        $pattern = '/<(?:[a-zA-Z0-9_]+:)?' . preg_quote($localName, '/') . '[^>]*>([\s\S]*?)<\/(?:[a-zA-Z0-9_]+:)?' . preg_quote($localName, '/') . '>/i';
        if (preg_match($pattern, $xml, $m)) {
            return trim($m[1]);
        }

        return '';
    }

    private static function extractOpeningTag(string $xml, string $localName): string
    {
        $pattern = '/<(?:[a-zA-Z0-9_]+:)?' . preg_quote($localName, '/') . '((?:\s+[^>]*)?)(?:\/?>|>)/i';
        if (preg_match($pattern, $xml, $m)) {
            return $m[0];
        }

        return '';
    }

    private static function extractAttr(string $xml, string $attrName): string
    {
        $pattern = '/' . preg_quote($attrName, '/') . '="([^"]*)"/i';
        if (preg_match($pattern, $xml, $m)) {
            return $m[1];
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private static function extractAllTags(string $xml, string $localName): array
    {
        $pattern = '/<(?:[a-zA-Z0-9_]+:)?' . preg_quote($localName, '/') . '[^>]*>([\s\S]*?)<\/(?:[a-zA-Z0-9_]+:)?' . preg_quote($localName, '/') . '>/i';
        preg_match_all($pattern, $xml, $matches, PREG_SET_ORDER);
        $results = [];
        foreach ($matches as $match) {
            $value = trim($match[1]);
            if ($value !== '') {
                $results[] = $value;
            }
        }

        return $results;
    }
}
