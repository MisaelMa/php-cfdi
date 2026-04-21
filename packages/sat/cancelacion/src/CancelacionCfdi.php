<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Sat\Cancelacion\Soap\AceptacionRechazo;
use Sat\Cancelacion\Soap\Cancelar;

/**
 * Cliente para los webservices de cancelación de CFDI del SAT.
 *
 * Implementa cancelación con CSD/FIEL, aceptación/rechazo y consulta de pendientes.
 */
final class CancelacionCfdi
{
    private const URL_CANCELAR = 'https://cancelacfd.sat.gob.mx/CancelaCFDService.svc';

    private const URL_ACEPTACION_RECHAZO = 'https://cancelacfd.sat.gob.mx/AceptacionRechazo/AceptacionRechazoService.svc';

    private const SOAP_ACTION_CANCELAR = 'http://cancelacfd.sat.gob.mx/ICancelaCFDService/CancelaCFD';

    private const SOAP_ACTION_ACEPTACION_RECHAZO = 'http://cancelacfd.sat.gob.mx/IAceptacionRechazoService/ProcesarRespuesta';

    private const SOAP_ACTION_PENDIENTES = 'http://cancelacfd.sat.gob.mx/IAceptacionRechazoService/ConsultaPendientes';

    private const TIMEOUT_SEC = 60;

    public function __construct(
        private readonly SatTokenLike $token,
        private readonly CredentialLike $credential,
    ) {
    }

    public function cancelar(CancelacionParams $params): CancelacionResult
    {
        $rfcEmisor = $params->rfcEmisor !== null && $params->rfcEmisor !== ''
            ? $params->rfcEmisor
            : $this->credential->rfc();

        $fecha = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s');

        $signed = $this->signComponents('CancelaCFD-' . $params->uuid);

        $cancelacionXml = Cancelar::buildCancelacionXml(
            $params,
            $rfcEmisor,
            $fecha,
            $signed['cert'],
            $signed['signatureValue'],
            $signed['serialNumber'],
        );

        $body = Cancelar::buildCancelarRequest(
            $cancelacionXml,
            $this->token->value(),
            $signed['cert'],
            $signed['signatureValue'],
        );

        $xml = $this->post(self::URL_CANCELAR, self::SOAP_ACTION_CANCELAR, $body);

        return Cancelar::parseCancelarResponse($xml);
    }

    public function aceptarRechazar(AceptacionRechazoParams $params): AceptacionRechazoResult
    {
        $fecha = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s');
        $signed = $this->signComponents('AceptacionRechazo-' . $params->uuid);

        $body = AceptacionRechazo::buildAceptacionRechazoRequest(
            $params,
            $this->token->value(),
            $signed['cert'],
            $signed['signatureValue'],
            $fecha,
        );

        $xml = $this->post(self::URL_ACEPTACION_RECHAZO, self::SOAP_ACTION_ACEPTACION_RECHAZO, $body);

        return AceptacionRechazo::parseAceptacionRechazoResponse($xml);
    }

    /**
     * @return list<PendientesResult>
     */
    public function consultarPendientes(): array
    {
        $rfcReceptor = $this->credential->rfc();
        $signed = $this->signComponents('ConsultaPendientes-' . $rfcReceptor);

        $body = AceptacionRechazo::buildConsultaPendientesRequest(
            $rfcReceptor,
            $this->token->value(),
            $signed['cert'],
            $signed['signatureValue'],
        );

        $xml = $this->post(self::URL_ACEPTACION_RECHAZO, self::SOAP_ACTION_PENDIENTES, $body);

        return AceptacionRechazo::parsePendientesResponse($xml);
    }

    /**
     * @return array{cert: string, signatureValue: string, serialNumber: string}
     */
    private function signComponents(string $content): array
    {
        $signatureValue = $this->credential->sign($content);
        $pemCert = $this->credential->certificate()->toPem();
        $cert = (string) preg_replace('/\s+/', '', str_replace(
            ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'],
            '',
            $pemCert
        ));
        $serialNumber = $this->credential->certificate()->serialNumber();

        return [
            'cert' => $cert,
            'signatureValue' => $signatureValue,
            'serialNumber' => $serialNumber,
        ];
    }

    private function post(string $url, string $soapAction, string $body): string
    {
        $header = implode("\r\n", [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "' . $soapAction . '"',
        ]) . "\r\n";

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $header,
                'content' => $body,
                'timeout' => self::TIMEOUT_SEC,
                'ignore_errors' => true,
            ],
        ]);

        $prevHandler = set_error_handler(static fn () => true);
        $result = file_get_contents($url, false, $ctx);
        restore_error_handler();

        if ($result === false) {
            $err = error_get_last();
            $msg = $err['message'] ?? 'error desconocido';
            if (stripos($msg, 'timed out') !== false) {
                throw new RuntimeException(
                    'Timeout: el webservice de cancelacion no respondio en ' . self::TIMEOUT_SEC . ' segundos'
                );
            }
            throw new RuntimeException('Error de red al conectar con el servicio de cancelacion: ' . $msg);
        }

        $statusLine = $http_response_header[0] ?? '';
        $code = 0;
        if (preg_match('#HTTP/\S+\s+(\d+)#', $statusLine, $m)) {
            $code = (int) $m[1];
        }

        if ($code < 200 || $code >= 300) {
            $text = preg_match('#\s(.+)$#', $statusLine, $tm) ? trim($tm[1]) : '';
            throw new RuntimeException(
                'El webservice de cancelacion retorno HTTP ' . $code . ($text !== '' ? ': ' . $text : '')
            );
        }

        return $result;
    }
}
