<?php

namespace Cfdi\Descarga\Soap;

use RuntimeException;

final class Descargar
{
    public static function buildDescargarRequest(
        string $idPaquete,
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
    <des:PeticionDescargaMasivaTercerosEntrada>
      <des:peticionDescarga IdPaquete="{$idPaquete}"
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
      </des:peticionDescarga>
    </des:PeticionDescargaMasivaTercerosEntrada>
  </s:Body>
</s:Envelope>
XML;
    }

    public static function parseDescargarResponse(string $xml): string
    {
        if (str_contains($xml, '<faultcode>') || str_contains($xml, ':Fault>')) {
            $faultString = self::extractTag($xml, 'faultstring');
            throw new RuntimeException(
                'SOAP Fault: ' . ($faultString !== '' ? $faultString : 'Error desconocido del servicio')
            );
        }

        $paqueteB64 = self::extractTag($xml, 'Paquete')
            ?: self::extractTag($xml, 'RespuestaDescargaMasivaTercerosSalida');

        if ($paqueteB64 === '') {
            throw new RuntimeException(
                'La respuesta del SAT no contiene el elemento Paquete con el ZIP'
            );
        }

        $decoded = base64_decode(preg_replace('/\s+/', '', $paqueteB64), true);
        if ($decoded === false) {
            throw new RuntimeException('No se pudo decodificar el paquete Base64');
        }

        return $decoded;
    }

    private static function extractTag(string $xml, string $localName): string
    {
        $pattern = '/<(?:[a-zA-Z0-9_]+:)?' . preg_quote($localName, '/') . '[^>]*>([\s\S]*?)<\/(?:[a-zA-Z0-9_]+:)?' . preg_quote($localName, '/') . '>/i';
        if (preg_match($pattern, $xml, $m)) {
            return trim($m[1]);
        }

        return '';
    }
}
