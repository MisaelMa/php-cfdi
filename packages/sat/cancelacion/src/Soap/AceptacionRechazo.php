<?php

declare(strict_types=1);

namespace Sat\Cancelacion\Soap;

use RuntimeException;
use Sat\Cancelacion\AceptacionRechazoParams;
use Sat\Cancelacion\AceptacionRechazoResult;
use Sat\Cancelacion\PendientesResult;

final class AceptacionRechazo
{
    public static function buildAceptacionRechazoRequest(
        AceptacionRechazoParams $params,
        string $token,
        string $cert,
        string $signatureValue,
        string $fecha,
    ): string {
        $rfc = $params->rfcReceptor;
        $uuid = $params->uuid;
        $respuesta = $params->respuesta->value;

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
  <s:Header>
    <o:Security xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                s:mustUnderstand="1">
      <u:Timestamp>
        <u:Created>{$token}</u:Created>
      </u:Timestamp>
      <o:BinarySecurityToken
        ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"
        EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">
        {$cert}
      </o:BinarySecurityToken>
    </o:Security>
  </s:Header>
  <s:Body>
    <ProcesarRespuesta xmlns="http://cancelacfd.sat.gob.mx/">
      <RfcReceptor>{$rfc}</RfcReceptor>
      <UUID>{$uuid}</UUID>
      <Respuesta>{$respuesta}</Respuesta>
      <Fecha>{$fecha}</Fecha>
      <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
        <SignedInfo>
          <CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
          <SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
          <Reference URI="">
            <Transforms>
              <Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
            </Transforms>
            <DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
            <DigestValue></DigestValue>
          </Reference>
        </SignedInfo>
        <SignatureValue>{$signatureValue}</SignatureValue>
        <KeyInfo>
          <X509Data>
            <X509Certificate>{$cert}</X509Certificate>
          </X509Data>
        </KeyInfo>
      </Signature>
    </ProcesarRespuesta>
  </s:Body>
</s:Envelope>
XML;
    }

    public static function buildConsultaPendientesRequest(
        string $rfcReceptor,
        string $token,
        string $cert,
        string $signatureValue, // unused in body; kept for parity with the Node.js API
    ): string {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
  <s:Header>
    <o:Security xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                s:mustUnderstand="1">
      <u:Timestamp>
        <u:Created>{$token}</u:Created>
      </u:Timestamp>
      <o:BinarySecurityToken
        ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"
        EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">
        {$cert}
      </o:BinarySecurityToken>
    </o:Security>
  </s:Header>
  <s:Body>
    <ConsultaPendientes xmlns="http://cancelacfd.sat.gob.mx/">
      <RfcReceptor>{$rfcReceptor}</RfcReceptor>
    </ConsultaPendientes>
  </s:Body>
</s:Envelope>
XML;
    }

    public static function parseAceptacionRechazoResponse(string $xml): AceptacionRechazoResult
    {
        if (str_contains($xml, '<faultcode>') || str_contains($xml, ':Fault>')) {
            $faultString = self::extractTag($xml, 'faultstring');
            throw new RuntimeException(
                'SOAP Fault: ' . ($faultString !== '' ? $faultString : 'Error desconocido del servicio')
            );
        }

        $uuid = self::extractAttr($xml, 'UUID') ?: self::extractTag($xml, 'UUID');
        $codEstatus = self::extractAttr($xml, 'CodEstatus') ?: self::extractTag($xml, 'CodEstatus');
        $mensaje = self::extractAttr($xml, 'Mensaje') ?: self::extractTag($xml, 'Mensaje');

        return new AceptacionRechazoResult($uuid, $codEstatus, $mensaje);
    }

    /**
     * @return list<PendientesResult>
     */
    public static function parsePendientesResponse(string $xml): array
    {
        if (str_contains($xml, '<faultcode>') || str_contains($xml, ':Fault>')) {
            $faultString = self::extractTag($xml, 'faultstring');
            throw new RuntimeException(
                'SOAP Fault: ' . ($faultString !== '' ? $faultString : 'Error desconocido del servicio')
            );
        }

        $results = [];
        $pattern = '/<(?:[a-zA-Z0-9_]+:)?UUID[^>]*>([^<]+)<\/(?:[a-zA-Z0-9_]+:)?UUID>/i';
        if (preg_match_all($pattern, $xml, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $i => $fullMatch) {
                $matchText = $fullMatch[0];
                $offset = $fullMatch[1];
                $uuidVal = trim($matches[1][$i][0]);
                $start = max(0, $offset - 500);
                $end = $offset + strlen($matchText) + 500;
                $block = substr($xml, $start, $end - $start);
                $results[] = new PendientesResult(
                    $uuidVal,
                    self::extractTag($block, 'RfcEmisor') ?: self::extractAttr($block, 'RfcEmisor'),
                    self::extractTag($block, 'FechaSolicitud') ?: self::extractAttr($block, 'FechaSolicitud'),
                );
            }
        }

        return $results;
    }

    private static function extractTag(string $xml, string $localName): string
    {
        $q = preg_quote($localName, '/');
        $pattern = '/<(?:[a-zA-Z0-9_]+:)?' . $q . '[^>]*>([\s\S]*?)<\/(?:[a-zA-Z0-9_]+:)?' . $q . '>/i';
        if (preg_match($pattern, $xml, $match)) {
            return trim($match[1]);
        }

        return '';
    }

    private static function extractAttr(string $xml, string $attrName): string
    {
        $q = preg_quote($attrName, '/');
        $pattern = '/' . $q . '="([^"]*)"/i';
        if (preg_match($pattern, $xml, $match)) {
            return $match[1];
        }

        return '';
    }
}
