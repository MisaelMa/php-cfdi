<?php

declare(strict_types=1);

namespace Sat\Cancelacion\Soap;

use RuntimeException;
use Sat\Cancelacion\CancelacionParams;
use Sat\Cancelacion\CancelacionResult;
use Sat\Cancelacion\EstatusCancelacion;
use Sat\Cancelacion\MotivoCancelacion;

final class Cancelar
{
    public static function buildCancelacionXml(
        CancelacionParams $params,
        string $rfcEmisor,
        string $fecha,
        string $cert,
        string $signatureValue,
        string $serialNumber,
    ): string {
        $folioAttr = '';
        if ($params->motivo === MotivoCancelacion::ConRelacion && $params->folioSustitucion !== null && $params->folioSustitucion !== '') {
            $folioAttr = ' FolioSustitucion="' . $params->folioSustitucion . '"';
        }

        $motivo = $params->motivo->value;

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<Cancelacion xmlns="http://cancelacfd.sat.gob.mx"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             RfcEmisor="{$rfcEmisor}"
             Fecha="{$fecha}">
  <Folios>
    <Folio UUID="{$params->uuid}"
           Motivo="{$motivo}"{$folioAttr}/>
  </Folios>
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
        <X509IssuerSerial>
          <X509SerialNumber>{$serialNumber}</X509SerialNumber>
        </X509IssuerSerial>
        <X509Certificate>{$cert}</X509Certificate>
      </X509Data>
    </KeyInfo>
  </Signature>
</Cancelacion>
XML;
    }

    public static function buildCancelarRequest(
        string $cancelacionXml,
        string $token,
        string $cert,
        string $signatureValue,
    ): string {
        $escaped = self::escapeXmlContent($cancelacionXml);

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
    <CancelaCFD xmlns="http://tempuri.org/">
      <Cancelacion>{$escaped}</Cancelacion>
    </CancelaCFD>
  </s:Body>
</s:Envelope>
XML;
    }

    public static function parseCancelarResponse(string $xml): CancelacionResult
    {
        if (str_contains($xml, '<faultcode>') || str_contains($xml, ':Fault>')) {
            $faultString = self::extractTag($xml, 'faultstring');
            throw new RuntimeException(
                'SOAP Fault: ' . ($faultString !== '' ? $faultString : 'Error desconocido del servicio de cancelacion')
            );
        }

        $folioTag = self::extractTag($xml, 'Folio')
            ?: self::extractTag($xml, 'CancelaCFDResult');

        $source = $folioTag !== '' ? $folioTag : $xml;
        $uuid = self::extractAttr($source, 'UUID') ?: self::extractAttr($xml, 'UUID');
        $estatusRaw = self::extractAttr($source, 'EstatusUUID') ?: self::extractAttr($xml, 'EstatusUUID');
        $codEstatus = self::extractAttr($xml, 'CodEstatus') ?: self::extractTag($xml, 'CodEstatus');
        $mensaje = self::extractAttr($xml, 'Mensaje') ?: self::extractTag($xml, 'Mensaje');

        /** @var array<string, EstatusCancelacion> $estatusMap */
        $estatusMap = [
            '201' => EstatusCancelacion::Cancelado,
            '202' => EstatusCancelacion::EnProceso,
            'Cancelado' => EstatusCancelacion::Cancelado,
            'EnProceso' => EstatusCancelacion::EnProceso,
        ];

        $estatus = $estatusMap[$estatusRaw] ?? EstatusCancelacion::EnProceso;

        return new CancelacionResult($uuid, $estatus, $codEstatus, $mensaje);
    }

    private static function escapeXmlContent(string $xml): string
    {
        return str_replace(
            ['&', '<', '>'],
            ['&amp;', '&lt;', '&gt;'],
            $xml
        );
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
