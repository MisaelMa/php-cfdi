<?php

declare(strict_types=1);

namespace Sat\Auth;

final class TokenBuilder
{
    /**
     * @param array{
     *   certificateBase64: string,
     *   created: string,
     *   expires: string,
     *   digest: string,
     *   signature: string,
     *   tokenId: string,
     * } $params
     */
    public static function buildAuthToken(array $params): string
    {
        $certificateBase64 = $params['certificateBase64'];
        $created = $params['created'];
        $expires = $params['expires'];
        $digest = $params['digest'];
        $signature = $params['signature'];
        $tokenId = $params['tokenId'];

        return '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">' .
            '<s:Header>' .
            '<o:Security xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" s:mustUnderstand="1">' .
            '<u:Timestamp u:Id="_0">' .
            '<u:Created>' . $created . '</u:Created>' .
            '<u:Expires>' . $expires . '</u:Expires>' .
            '</u:Timestamp>' .
            '<o:BinarySecurityToken u:Id="' . $tokenId . '" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">' . $certificateBase64 . '</o:BinarySecurityToken>' .
            '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">' .
            '<SignedInfo>' .
            '<CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>' .
            '<SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>' .
            '<Reference URI="#_0">' .
            '<Transforms>' .
            '<Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>' .
            '</Transforms>' .
            '<DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>' .
            '<DigestValue>' . $digest . '</DigestValue>' .
            '</Reference>' .
            '</SignedInfo>' .
            '<SignatureValue>' . $signature . '</SignatureValue>' .
            '<KeyInfo>' .
            '<o:SecurityTokenReference>' .
            '<o:Reference ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" URI="#' . $tokenId . '"/>' .
            '</o:SecurityTokenReference>' .
            '</KeyInfo>' .
            '</Signature>' .
            '</o:Security>' .
            '</s:Header>' .
            '<s:Body>' .
            '<Autentica xmlns="http://DescargaMasivaTerceros.gob.mx"/>' .
            '</s:Body>' .
            '</s:Envelope>';
    }

    public static function buildTimestampFragment(string $created, string $expires): string
    {
        return '<u:Timestamp xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" u:Id="_0">' .
            '<u:Created>' . $created . '</u:Created>' .
            '<u:Expires>' . $expires . '</u:Expires>' .
            '</u:Timestamp>';
    }

    public static function buildSignedInfoFragment(string $digest): string
    {
        return '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">' .
            '<CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>' .
            '<SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>' .
            '<Reference URI="#_0">' .
            '<Transforms>' .
            '<Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>' .
            '</Transforms>' .
            '<DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>' .
            '<DigestValue>' . $digest . '</DigestValue>' .
            '</Reference>' .
            '</SignedInfo>';
    }
}
