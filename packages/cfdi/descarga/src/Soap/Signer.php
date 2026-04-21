<?php

namespace Cfdi\Descarga\Soap;

use Cfdi\Descarga\CredentialLike;
use RuntimeException;

final class Signer
{
    public static function canonicalize(string $xml): string
    {
        $withoutDecl = preg_replace('/<\?xml[^?]*\?>\s*/', '', $xml);

        return trim($withoutDecl ?? $xml);
    }

    public static function digestSha256(string $content): string
    {
        $raw = openssl_digest($content, 'sha256', true);
        if ($raw === false) {
            throw new RuntimeException('openssl_digest fallo');
        }

        return base64_encode($raw);
    }

    public static function signSoapBody(
        string $bodyXml,
        CredentialLike $credential,
        string $bodyId = '_0',
    ): SoapSignatureComponents {
        $canonBody = self::canonicalize($bodyXml);
        $bodyDigest = self::digestSha256($canonBody);

        $signedInfo = self::buildSignedInfo($bodyDigest, $bodyId);
        $canonSignedInfo = self::canonicalize($signedInfo);
        $signatureValue = $credential->sign($canonSignedInfo);

        $pemCert = $credential->certificate()->toPem();
        $x509Certificate = preg_replace('/\s+/', '', str_replace(
            ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'],
            '',
            $pemCert
        ));

        return new SoapSignatureComponents(
            $bodyDigest,
            $signatureValue,
            $x509Certificate,
            $bodyId,
        );
    }

    public static function buildSecurityHeader(
        SoapSignatureComponents $components,
        string $tokenValue,
    ): string {
        $bodyDigest = $components->bodyDigest;
        $signatureValue = $components->signatureValue;
        $x509Certificate = $components->x509Certificate;
        $bodyId = $components->bodyId;

        $signedInfo = self::buildSignedInfo($bodyDigest, $bodyId);
        $keyId = substr($x509Certificate, 0, 40);

        return <<<XML
<s:Header>
  <h:Security xmlns:h="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
              xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
    <u:Timestamp>
      <u:Created>{$tokenValue}</u:Created>
    </u:Timestamp>
    <ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      {$signedInfo}
      <ds:SignatureValue>{$signatureValue}</ds:SignatureValue>
      <ds:KeyInfo>
        <o:SecurityTokenReference xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
          <o:KeyIdentifier ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3SubjectKeyIdentifier"
                           EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">
            {$keyId}
          </o:KeyIdentifier>
        </o:SecurityTokenReference>
      </ds:KeyInfo>
    </ds:Signature>
  </h:Security>
</s:Header>
XML;
    }

    private static function buildSignedInfo(string $bodyDigest, string $bodyId): string
    {
        return '<ds:SignedInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">'
            . '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>'
            . '<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>'
            . '<ds:Reference URI="#' . $bodyId . '">'
            . '<ds:Transforms>'
            . '<ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>'
            . '</ds:Transforms>'
            . '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>'
            . '<ds:DigestValue>' . $bodyDigest . '</ds:DigestValue>'
            . '</ds:Reference>'
            . '</ds:SignedInfo>';
    }
}
