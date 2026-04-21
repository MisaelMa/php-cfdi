<?php

declare(strict_types=1);

namespace Sat\Auth;

final class SatAuth
{
    public const AUTH_URL = 'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/Autenticacion/Autenticacion.svc';

    public const SOAP_ACTION = 'http://DescargaMasivaTerceros.gob.mx/IAutenticacion/Autentica';

    public function __construct(
        private readonly CredentialLike $credential,
    ) {
    }

    public function authenticate(): SatToken
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $expires = $now->modify('+5 minutes');
        $envelope = $this->createEnvelope($now, $expires);
        $body = $this->postSoap($envelope);

        return self::parseSoapResponse($body, $now, $expires);
    }

    public function buildAuthenticationEnvelope(): string
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $expires = $now->modify('+5 minutes');

        return $this->createEnvelope($now, $expires);
    }

    public static function parseSoapResponse(
        string $soapResponse,
        \DateTimeImmutable $created,
        \DateTimeImmutable $expires,
    ): SatToken {
        $value = null;
        if (preg_match('/<AutenticaResult>([^<]+)<\/AutenticaResult>/', $soapResponse, $m1)) {
            $value = trim($m1[1]);
        } elseif (preg_match('/<[^:]*:?AutenticaResult[^>]*>([^<]+)<\/[^:]*:?AutenticaResult>/', $soapResponse, $m2)) {
            $value = trim($m2[1]);
        }

        if ($value === null) {
            throw new \RuntimeException(
                'No se pudo extraer el token de la respuesta del SAT. Respuesta: ' . substr($soapResponse, 0, 500)
            );
        }

        if ($value === '') {
            throw new \RuntimeException('El token retornado por el SAT esta vacio.');
        }

        return new SatToken($value, $created, $expires);
    }

    private function createEnvelope(\DateTimeImmutable $now, \DateTimeImmutable $expires): string
    {
        $created = self::toIsoString($now);
        $expiresStr = self::toIsoString($expires);
        $tokenId = self::randomTokenId();

        $timestampFragment = TokenBuilder::buildTimestampFragment($created, $expiresStr);
        $canonicalTimestamp = XmlSigner::canonicalize($timestampFragment);
        $digest = XmlSigner::sha256Digest($canonicalTimestamp);

        $signedInfoFragment = TokenBuilder::buildSignedInfoFragment($digest);
        $canonicalSignedInfo = XmlSigner::canonicalize($signedInfoFragment);
        $signature = $this->credential->sign($canonicalSignedInfo);

        $certificateBase64 = self::pemCertificateToDerBase64($this->credential->getCertificatePem());

        return TokenBuilder::buildAuthToken([
            'certificateBase64' => $certificateBase64,
            'created' => $created,
            'expires' => $expiresStr,
            'digest' => $digest,
            'signature' => $signature,
            'tokenId' => $tokenId,
        ]);
    }

    private function postSoap(string $envelope): string
    {
        $headers = [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: ' . self::SOAP_ACTION,
        ];

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $envelope,
                'timeout' => 120,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents(self::AUTH_URL, false, $ctx);

        if ($body === false) {
            throw new \RuntimeException('SAT auth request failed: could not read response');
        }

        $responseHeaders = $http_response_header ?? [];
        $statusLine = $responseHeaders[0] ?? '';
        if (preg_match('#HTTP/\S+\s+(\d+)#', $statusLine, $sm) && (int) $sm[1] !== 200) {
            throw new \RuntimeException(
                'SAT auth request failed: HTTP ' . $sm[1] . '. Body: ' . $body
            );
        }

        return $body;
    }

    private static function pemCertificateToDerBase64(string $pem): string
    {
        $pem = trim($pem);
        if (! str_contains($pem, 'BEGIN CERTIFICATE')) {
            throw new \InvalidArgumentException('Certificate PEM must contain BEGIN CERTIFICATE');
        }
        $b64 = preg_replace('#-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+#', '', $pem) ?? '';
        $der = base64_decode($b64, true);
        if ($der === false) {
            throw new \InvalidArgumentException('Invalid certificate PEM body');
        }

        return base64_encode($der);
    }

    private static function toIsoString(\DateTimeImmutable $date): string
    {
        $utc = $date->setTimezone(new \DateTimeZone('UTC'));
        $micros = (int) $utc->format('u');
        $ms = intdiv($micros, 1000);

        return $utc->format('Y-m-d\TH:i:s') . sprintf('.%03d', $ms) . 'Z';
    }

    private static function randomTokenId(): string
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
        $h = bin2hex($b);

        return 'uuid-' . substr($h, 0, 8) . '-' . substr($h, 8, 4) . '-' . substr($h, 12, 4) . '-' . substr($h, 16, 4) . '-' . substr($h, 20, 12);
    }
}
