<?php

declare(strict_types=1);

namespace Sat\Pacs\Providers;

use Sat\Pacs\CancelacionPacResult;
use Sat\Pacs\ConsultaEstatusResult;
use Sat\Pacs\PacConfig;
use Sat\Pacs\PacProvider;
use Sat\Pacs\TimbradoRequest;
use Sat\Pacs\TimbradoResult;

/**
 * Cliente Finkok: timbrado, cancelación y consulta vía SOAP sobre los WSDL públicos.
 * Las URLs de producción y demo siguen la documentación de Finkok.
 */
class FinkokProvider implements PacProvider
{
    private const string NS_SOAP = 'http://schemas.xmlsoap.org/soap/envelope/';

    private const string NS_STAMP = 'http://facturacion.finkok.com/stamp';

    private const string NS_CANCEL = 'http://facturacion.finkok.com/cancel';

    private const string PROD_STAMP_WSDL = 'https://facturacion.finkok.com/servicios/soap/stamp.wsdl';

    private const string DEMO_STAMP_WSDL = 'https://demo-facturacion.finkok.com/servicios/soap/stamp.wsdl';

    public function __construct(
        private readonly PacConfig $config,
    ) {
    }

    public function timbrar(TimbradoRequest $request): TimbradoResult
    {
        $b64 = base64_encode($request->xml);
        $inner = '
    <stamp:stamp xmlns:stamp="' . self::NS_STAMP . '">
      <stamp:xml>' . $b64 . '</stamp:xml>
      ' . $this->authStamp() . '
    </stamp:stamp>';
        $response = $this->postSoap($this->stampUrl(), self::soapEnvelope($inner));

        $cod = self::pickLocalText($response, 'CodEstatus');
        $err = self::pickLocalText($response, 'MensajeIncidencia') ?? self::pickLocalText($response, 'error');
        $fail = ($err !== null && $err !== '')
            || (($cod !== null && $cod !== '') && ! preg_match('/timbrado satisfactoriamente/i', $cod));
        if ($fail) {
            $message = ($err !== null && $err !== '') ? $err : (($cod !== null && $cod !== '') ? $cod : 'Error desconocido en timbrado Finkok.');
            throw new \RuntimeException($message);
        }

        $xmlB64 = self::pickLocalText($response, 'xml');
        if ($xmlB64 === null || $xmlB64 === '') {
            throw new \RuntimeException('Respuesta de timbrado sin nodo xml.');
        }
        $xmlTimbrado = base64_decode($xmlB64, true);
        if ($xmlTimbrado === false) {
            throw new \RuntimeException('El XML timbrado en base64 no es válido.');
        }
        $timbre = self::extractTimbreFields($xmlTimbrado);

        return new TimbradoResult(
            uuid: $timbre['uuid'],
            fecha: $timbre['fechaTimbrado'],
            selloCFD: $timbre['selloCFD'],
            selloSAT: $timbre['selloSAT'],
            noCertificadoSAT: $timbre['noCertificadoSAT'],
            cadenaOriginalSAT: $timbre['cadenaOriginalSAT'],
            xml: $xmlTimbrado,
        );
    }

    public function cancelar(
        string $uuid,
        string $rfcEmisor,
        string $motivo,
        ?string $folioSustitucion = null,
    ): CancelacionPacResult {
        $folioAttr = ($folioSustitucion !== null && $folioSustitucion !== '')
            ? ' FolioSustitucion="' . self::escapeXml($folioSustitucion) . '"'
            : '';
        $inner = '
    <cancel:cancel xmlns:cancel="' . self::NS_CANCEL . '">
      <cancel:UUIDS>
        <cancel:UUID UUID="' . self::escapeXml($uuid) . '" Motivo="' . self::escapeXml($motivo) . '"' . $folioAttr . '/>
      </cancel:UUIDS>
      ' . $this->authCancel() . '
      <cancel:taxpayer_id>' . self::escapeXml($rfcEmisor) . '</cancel:taxpayer_id>
    </cancel:cancel>';
        $response = $this->postSoap($this->cancelUrl(), self::soapEnvelope($inner));

        $wsErr = self::pickLocalText($response, 'error');
        if ($wsErr !== null && $wsErr !== '') {
            throw new \RuntimeException($wsErr);
        }

        $acuse = self::pickLocalText($response, 'Acuse') ?? '';
        $estatus = self::pickLocalText($response, 'EstatusUUID')
            ?? self::pickLocalText($response, 'EstatusCancelacion')
            ?? self::pickLocalText($response, 'CodEstatus')
            ?? '';

        return new CancelacionPacResult(
            uuid: $uuid,
            estatus: $estatus,
            acuse: $acuse,
        );
    }

    public function consultarEstatus(string $uuid): ConsultaEstatusResult
    {
        $inner = '
    <stamp:query_pending xmlns:stamp="' . self::NS_STAMP . '">
      ' . $this->authStamp() . '
      <stamp:uuid>' . self::escapeXml($uuid) . '</stamp:uuid>
    </stamp:query_pending>';
        $response = $this->postSoap($this->stampUrl(), self::soapEnvelope($inner));

        $err = self::pickLocalText($response, 'error');
        if ($err !== null && $err !== '') {
            throw new \RuntimeException($err);
        }

        $estatus = self::pickLocalText($response, 'status') ?? '';
        $xmlRaw = self::pickLocalText($response, 'xml');
        $uuidOut = self::pickLocalText($response, 'uuid') ?? $uuid;

        return new ConsultaEstatusResult(
            uuid: $uuidOut,
            estatus: $estatus,
            xml: $xmlRaw !== null && $xmlRaw !== '' ? self::decodeBasicEntities($xmlRaw) : null,
        );
    }

    private function finkokOrigin(): string
    {
        $raw = $this->config->url !== null ? trim($this->config->url) : '';
        if ($raw === '') {
            return $this->config->sandbox
                ? 'https://demo-facturacion.finkok.com'
                : 'https://facturacion.finkok.com';
        }
        $withProto = preg_match('#^https?://#i', $raw) === 1 ? $raw : 'https://' . $raw;
        $parsed = parse_url($withProto);
        if (! is_array($parsed) || ! isset($parsed['scheme'], $parsed['host'])) {
            return $this->config->sandbox
                ? 'https://demo-facturacion.finkok.com'
                : 'https://facturacion.finkok.com';
        }

        return $parsed['scheme'] . '://' . $parsed['host']
            . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
    }

    private function stampUrl(): string
    {
        $raw = $this->config->url !== null ? trim($this->config->url) : '';
        if ($raw !== '' && preg_match('/\.wsdl$/i', $raw) === 1 && preg_match('/stamp/i', $raw) === 1) {
            return preg_match('#^https?://#i', $raw) === 1 ? $raw : 'https://' . $raw;
        }

        return $this->config->sandbox ? self::DEMO_STAMP_WSDL : self::PROD_STAMP_WSDL;
    }

    private function cancelUrl(): string
    {
        $raw = $this->config->url !== null ? trim($this->config->url) : '';
        if ($raw !== '' && preg_match('/\.wsdl$/i', $raw) === 1 && preg_match('/cancel/i', $raw) === 1) {
            return preg_match('#^https?://#i', $raw) === 1 ? $raw : 'https://' . $raw;
        }

        return $this->finkokOrigin() . '/servicios/soap/cancel.wsdl';
    }

    private function authStamp(): string
    {
        return '
      <stamp:username>' . self::escapeXml($this->config->user) . '</stamp:username>
      <stamp:password>' . self::escapeXml($this->config->password) . '</stamp:password>';
    }

    private function authCancel(): string
    {
        return '
      <cancel:username>' . self::escapeXml($this->config->user) . '</cancel:username>
      <cancel:password>' . self::escapeXml($this->config->password) . '</cancel:password>';
    }

    protected function postSoap(string $url, string $xml): string
    {
        $headers = [
            'Content-Type: text/xml; charset=utf-8',
        ];
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $xml,
                'timeout' => 120,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            throw new \RuntimeException('Finkok SOAP request failed: could not read response');
        }

        $responseHeaders = $http_response_header ?? [];
        $statusLine = $responseHeaders[0] ?? '';
        if (preg_match('#HTTP/\S+\s+(\d+)#', $statusLine, $sm) === 1 && (int) $sm[1] !== 200) {
            throw new \RuntimeException('Finkok HTTP ' . $sm[1] . ': ' . substr($body, 0, 500));
        }

        return $body;
    }

    private static function soapEnvelope(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="' . self::NS_SOAP . '">
  <soapenv:Header/>
  <soapenv:Body>
    ' . $body . '
  </soapenv:Body>
</soapenv:Envelope>';
    }

    private static function escapeXml(string $text): string
    {
        return str_replace(['&', '<', '>', '"'], ['&amp;', '&lt;', '&gt;', '&quot;'], $text);
    }

    private static function decodeBasicEntities(string $text): string
    {
        $text = str_replace('&lt;', '<', $text);
        $text = str_replace('&gt;', '>', $text);
        $text = str_replace('&quot;', '"', $text);
        $text = str_replace('&apos;', "'", $text);
        $text = str_replace('&amp;', '&', $text);

        return $text;
    }

    private static function pickLocalText(string $xml, string $localName): ?string
    {
        $q = preg_quote($localName, '/');
        $re = '/<(?:[\w.-]+:)?' . $q . '[^>]*>([\s\S]*?)<\/(?:[\w.-]+:)?' . $q . '>/i';
        if (preg_match($re, $xml, $m) !== 1) {
            return null;
        }

        return self::decodeBasicEntities(trim($m[1]));
    }

    private static function pickAttr(string $xml, string $attr): ?string
    {
        $re = '/' . preg_quote($attr, '/') . '="([^"]*)"/i';
        if (preg_match($re, $xml, $m) !== 1) {
            return null;
        }

        return $m[1];
    }

    /**
     * @return array{uuid: string, fechaTimbrado: string, selloCFD: string, selloSAT: string, noCertificadoSAT: string, cadenaOriginalSAT: string}
     */
    private static function extractTimbreFields(string $xmlTimbrado): array
    {
        $matched = preg_match('/<(?:[^:>]+:)?TimbreFiscalDigital\b[^>]*\/?>/', $xmlTimbrado, $tm) === 1
            || preg_match('/<TimbreFiscalDigital\b[^>]*\/?>/', $xmlTimbrado, $tm) === 1;
        if (! $matched) {
            throw new \RuntimeException('El XML timbrado no contiene TimbreFiscalDigital.');
        }
        $timbre = $tm[0];
        $uuid = self::pickAttr($timbre, 'UUID');
        $fechaTimbrado = self::pickAttr($timbre, 'FechaTimbrado');
        $selloCFD = self::pickAttr($timbre, 'SelloCFD') ?? '';
        $selloSAT = self::pickAttr($timbre, 'SelloSAT') ?? '';
        $noCertificadoSAT = self::pickAttr($timbre, 'NoCertificadoSAT') ?? '';
        $cadenaOriginalSAT = self::pickAttr($timbre, 'CadenaOriginal') ?? '';
        if ($uuid === null || $uuid === '' || $fechaTimbrado === null || $fechaTimbrado === '') {
            throw new \RuntimeException('No se pudieron leer UUID o FechaTimbrado del timbre.');
        }

        return [
            'uuid' => $uuid,
            'fechaTimbrado' => $fechaTimbrado,
            'selloCFD' => $selloCFD,
            'selloSAT' => $selloSAT,
            'noCertificadoSAT' => $noCertificadoSAT,
            'cadenaOriginalSAT' => $cadenaOriginalSAT,
        ];
    }
}
