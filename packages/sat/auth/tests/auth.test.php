<?php

declare(strict_types=1);

use Sat\Auth\CredentialLike;
use Sat\Auth\SatAuth;
use Sat\Auth\SatToken;
use Sat\Auth\TokenBuilder;
use Sat\Auth\XmlSigner;

function makeTestCredential(): CredentialLike
{
    $cfg = [
        'digest_alg' => 'sha256',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];
    $priv = openssl_pkey_new($cfg);
    if ($priv === false) {
        throw new RuntimeException((string) openssl_error_string());
    }
    $dn = ['CN' => 'Test SAT Auth'];
    $csr = openssl_csr_new($dn, $priv, $cfg);
    if ($csr === false) {
        throw new RuntimeException((string) openssl_error_string());
    }
    $cert = openssl_csr_sign($csr, null, $priv, 1, $cfg);
    if ($cert === false) {
        throw new RuntimeException((string) openssl_error_string());
    }
    $pem = '';
    if (! openssl_x509_export($cert, $pem)) {
        throw new RuntimeException('openssl_x509_export failed');
    }

    return new class ($priv, $pem) implements CredentialLike {
        public function __construct(
            private readonly \OpenSSLAsymmetricKey $priv,
            private readonly string $pem,
        ) {
        }

        public function sign(string $data): string
        {
            return XmlSigner::signRsaSha256($data, $this->priv);
        }

        public function getCertificatePem(): string
        {
            return $this->pem;
        }

        public function getRfc(): string
        {
            return 'TST010101AAA';
        }
    };
}

describe('canonicalize', function () {
    test('elimina la declaracion XML', function () {
        $input = '<?xml version="1.0" encoding="UTF-8"?><root/>';
        $result = XmlSigner::canonicalize($input);
        expect($result)->not->toContain('<?xml');
        expect($result)->toContain('<root');
    });

    test('ordena atributos alfabeticamente', function () {
        $input = '<elem z="3" a="1" m="2"/>';
        $result = XmlSigner::canonicalize($input);
        $aIdx = strpos($result, 'a=');
        $mIdx = strpos($result, 'm=');
        $zIdx = strpos($result, 'z=');
        expect($aIdx)->toBeLessThan($mIdx);
        expect($mIdx)->toBeLessThan($zIdx);
    });

    test('conserva el contenido de texto', function () {
        $input =
            '<u:Timestamp xmlns:u="http://example.com" u:Id="_0">' .
            '<u:Created>2024-01-01T00:00:00.000Z</u:Created>' .
            '<u:Expires>2024-01-01T00:05:00.000Z</u:Expires>' .
            '</u:Timestamp>';
        $result = XmlSigner::canonicalize($input);
        expect($result)->toContain('2024-01-01T00:00:00.000Z');
        expect($result)->toContain('2024-01-01T00:05:00.000Z');
    });

    test('normaliza saltos de linea CRLF a LF', function () {
        $input = "<root>\r\n<child/>\r\n</root>";
        $result = XmlSigner::canonicalize($input);
        expect($result)->not->toContain("\r\n");
        expect($result)->toContain("\n");
    });

    test('maneja XML sin atributos', function () {
        $input = '<root><child>texto</child></root>';
        $result = XmlSigner::canonicalize($input);
        expect($result)->toBe('<root><child>texto</child></root>');
    });

    test('ordena atributos del Timestamp alfabeticamente (u:Id antes que xmlns:u)', function () {
        $input =
            '<u:Timestamp xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" u:Id="_0">' .
            '<u:Created>2024-01-01T00:00:00.000Z</u:Created>' .
            '</u:Timestamp>';
        $result = XmlSigner::canonicalize($input);
        $idIdx = strpos($result, 'u:Id=');
        $xmlnsIdx = strpos($result, 'xmlns:u=');
        expect($idIdx)->toBeLessThan($xmlnsIdx);
    });
});

describe('sha256Digest', function () {
    test('retorna base64 valido', function () {
        $result = XmlSigner::sha256Digest('hello world');
        expect($result)->toMatch('/^[A-Za-z0-9+\/]+=*$/');
    });

    test('retorna el hash correcto para string conocido', function () {
        $expected = 'uU0nuZNNPgilLlLX2n2r+sSE7+N6U4DukIj3rOLvzek=';
        expect(XmlSigner::sha256Digest('hello world'))->toBe($expected);
    });

    test('retorna resultados distintos para strings distintos', function () {
        $a = XmlSigner::sha256Digest('abc');
        $b = XmlSigner::sha256Digest('xyz');
        expect($a)->not->toBe($b);
    });

    test('es determinista', function () {
        $a = XmlSigner::sha256Digest('test-data');
        $b = XmlSigner::sha256Digest('test-data');
        expect($a)->toBe($b);
    });
});

describe('signRsaSha256', function () {
    test('produce una firma verificable con la llave publica correspondiente', function () {
        $cfg = ['digest_alg' => 'sha256', 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $priv = openssl_pkey_new($cfg);
        expect($priv)->not->toBeFalse();
        $details = openssl_pkey_get_details($priv);
        expect($details)->toBeArray();
        $pubPem = $details['key'];
        $data = 'datos de prueba para firma';
        $signature = XmlSigner::signRsaSha256($data, $priv);
        expect($signature)->toMatch('/^[A-Za-z0-9+\/]+=*$/');
        $pub = openssl_pkey_get_public($pubPem);
        expect($pub)->not->toBeFalse();
        $sigRaw = base64_decode($signature, true);
        expect($sigRaw)->not->toBeFalse();
        $ok = openssl_verify($data, $sigRaw, $pub, OPENSSL_ALGO_SHA256);
        expect($ok)->toBe(1);
    });

    test('retorna base64 no vacio', function () {
        $cfg = ['digest_alg' => 'sha256', 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $priv = openssl_pkey_new($cfg);
        expect($priv)->not->toBeFalse();
        $result = XmlSigner::signRsaSha256('test', $priv);
        expect(strlen($result))->toBeGreaterThan(0);
    });

    test('firmas distintas para datos distintos', function () {
        $cfg = ['digest_alg' => 'sha256', 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $priv = openssl_pkey_new($cfg);
        expect($priv)->not->toBeFalse();
        $a = XmlSigner::signRsaSha256('datos A', $priv);
        $b = XmlSigner::signRsaSha256('datos B', $priv);
        expect($a)->not->toBe($b);
    });
});

describe('buildAuthToken', function () {
    $sample = [
        'certificateBase64' => 'CERTBASE64==',
        'created' => '2024-01-01T00:00:00.000Z',
        'expires' => '2024-01-01T00:05:00.000Z',
        'digest' => 'DIGESTBASE64==',
        'signature' => 'SIGNATUREBASE64==',
        'tokenId' => 'uuid-1234-5678',
    ];

    test('contiene el namespace SOAP correcto', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"');
    });

    test('contiene el namespace WSS utility', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('xmlns:u="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"');
    });

    test('contiene el namespace WSS secext', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"');
    });

    test('incluye las fechas de Created y Expires', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('<u:Created>2024-01-01T00:00:00.000Z</u:Created>');
        expect($xml)->toContain('<u:Expires>2024-01-01T00:05:00.000Z</u:Expires>');
    });

    test('incluye el BinarySecurityToken con el certificado', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('CERTBASE64==');
        expect($xml)->toContain('o:BinarySecurityToken');
    });

    test('incluye el tokenId en el BinarySecurityToken', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('u:Id="uuid-1234-5678"');
    });

    test('incluye el DigestValue', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('<DigestValue>DIGESTBASE64==</DigestValue>');
    });

    test('incluye el SignatureValue', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('<SignatureValue>SIGNATUREBASE64==</SignatureValue>');
    });

    test('incluye el algoritmo de canonicalizacion C14N', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"');
    });

    test('incluye el algoritmo de firma RSA-SHA256', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"');
    });

    test('incluye el algoritmo de digest SHA-256', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"');
    });

    test('incluye la referencia al Timestamp con URI #_0', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('URI="#_0"');
    });

    test('incluye el elemento Autentica en el Body', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('<Autentica xmlns="http://DescargaMasivaTerceros.gob.mx"/>');
    });

    test('incluye SecurityTokenReference apuntando al tokenId', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toContain('URI="#uuid-1234-5678"');
    });

    test('produce XML bien formado (apertura y cierre de Envelope)', function () use ($sample) {
        $xml = TokenBuilder::buildAuthToken($sample);
        expect($xml)->toMatch('/^<s:Envelope/');
        expect($xml)->toMatch('/<\/s:Envelope>$/');
    });
});

describe('buildTimestampFragment', function () {
    test('incluye el elemento Timestamp con Id _0', function () {
        $result = TokenBuilder::buildTimestampFragment(
            '2024-01-01T00:00:00.000Z',
            '2024-01-01T00:05:00.000Z'
        );
        expect($result)->toContain('u:Id="_0"');
    });

    test('incluye el namespace WSS utility en el Timestamp', function () {
        $result = TokenBuilder::buildTimestampFragment(
            '2024-01-01T00:00:00.000Z',
            '2024-01-01T00:05:00.000Z'
        );
        expect($result)->toContain('xmlns:u=');
    });

    test('incluye Created y Expires con los valores correctos', function () {
        $result = TokenBuilder::buildTimestampFragment(
            '2024-06-15T12:00:00.000Z',
            '2024-06-15T12:05:00.000Z'
        );
        expect($result)->toContain('2024-06-15T12:00:00.000Z');
        expect($result)->toContain('2024-06-15T12:05:00.000Z');
    });
});

describe('buildSignedInfoFragment', function () {
    test('incluye el DigestValue proporcionado', function () {
        $result = TokenBuilder::buildSignedInfoFragment('miDigest==');
        expect($result)->toContain('<DigestValue>miDigest==</DigestValue>');
    });

    test('incluye el namespace xmldsig', function () {
        $result = TokenBuilder::buildSignedInfoFragment('digest');
        expect($result)->toContain('xmlns="http://www.w3.org/2000/09/xmldsig#"');
    });

    test('incluye la referencia a _0', function () {
        $result = TokenBuilder::buildSignedInfoFragment('digest');
        expect($result)->toContain('URI="#_0"');
    });
});

describe('SatAuth', function () {
    test('AUTH_URL es el endpoint de autenticacion del SAT', function () {
        expect(SatAuth::AUTH_URL)->toBe(
            'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/Autenticacion/Autenticacion.svc'
        );
    });

    test('SOAP_ACTION es el valor esperado', function () {
        expect(SatAuth::SOAP_ACTION)->toBe(
            'http://DescargaMasivaTerceros.gob.mx/IAutenticacion/Autentica'
        );
    });

    test('buildAuthenticationEnvelope contiene Autentica y namespace DescargaMasivaTerceros', function () {
        $auth = new SatAuth(makeTestCredential());
        $body = $auth->buildAuthenticationEnvelope();
        expect($body)->toContain('Autentica');
        expect($body)->toContain('http://DescargaMasivaTerceros.gob.mx');
    });

    test('buildAuthenticationEnvelope contiene BinarySecurityToken', function () {
        $auth = new SatAuth(makeTestCredential());
        $body = $auth->buildAuthenticationEnvelope();
        expect($body)->toContain('BinarySecurityToken');
    });

    test('buildAuthenticationEnvelope Created y Expires difieren cinco minutos', function () {
        $auth = new SatAuth(makeTestCredential());
        $body = $auth->buildAuthenticationEnvelope();
        expect(preg_match('/<u:Created>([^<]+)<\/u:Created>/', $body, $c))->toBe(1);
        expect(preg_match('/<u:Expires>([^<]+)<\/u:Expires>/', $body, $e))->toBe(1);
        $created = new DateTimeImmutable($c[1]);
        $expires = new DateTimeImmutable($e[1]);
        expect($expires->getTimestamp() - $created->getTimestamp())->toBe(300);
    });

    test('parseSoapResponse extrae el token', function () {
        $soap = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <AutenticaResponse xmlns="http://DescargaMasivaTerceros.gob.mx">
      <AutenticaResult>TOKENVALUEFROMSAT123456789</AutenticaResult>
    </AutenticaResponse>
  </s:Body>
</s:Envelope>
XML;
        $created = new DateTimeImmutable('2024-01-01T00:00:00Z');
        $expires = new DateTimeImmutable('2024-01-01T00:05:00Z');
        $token = SatAuth::parseSoapResponse($soap, $created, $expires);
        expect($token)->toBeInstanceOf(SatToken::class);
        expect($token->value)->toBe('TOKENVALUEFROMSAT123456789');
        expect($token->created->getTimestamp())->toBe($created->getTimestamp());
        expect($token->expires->getTimestamp())->toBe($expires->getTimestamp());
    });

    test('parseSoapResponse maneja namespace en AutenticaResult', function () {
        $soap = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <d:AutenticaResponse xmlns:d="http://DescargaMasivaTerceros.gob.mx">
      <d:AutenticaResult>TOKEN_CON_PREFIJO</d:AutenticaResult>
    </d:AutenticaResponse>
  </s:Body>
</s:Envelope>
XML;
        $created = new DateTimeImmutable('2024-03-01T00:00:00Z');
        $expires = new DateTimeImmutable('2024-03-01T00:05:00Z');
        $token = SatAuth::parseSoapResponse($soap, $created, $expires);
        expect($token->value)->toBe('TOKEN_CON_PREFIJO');
    });

    test('parseSoapResponse lanza si falta AutenticaResult', function () {
        $soap = '<s:Envelope><s:Body></s:Body></s:Envelope>';
        $created = new DateTimeImmutable('now');
        $expires = $created->modify('+5 minutes');
        SatAuth::parseSoapResponse($soap, $created, $expires);
    })->throws(RuntimeException::class, 'No se pudo extraer el token');
});
