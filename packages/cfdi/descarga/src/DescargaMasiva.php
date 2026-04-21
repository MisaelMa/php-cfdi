<?php

namespace Cfdi\Descarga;

use Cfdi\Descarga\Soap\Descargar;
use Cfdi\Descarga\Soap\Solicitar;
use Cfdi\Descarga\Soap\Verificar;
use RuntimeException;

final class DescargaMasiva
{
    private const string URL_SOLICITAR = 'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/SolicitaDescargaService.svc';

    private const string URL_VERIFICAR = 'https://cfdidescargamasivasolicitud.clouda.sat.gob.mx/VerificaSolicitudDescargaService.svc';

    private const string URL_DESCARGAR = 'https://cfdidescargamasiva.clouda.sat.gob.mx/DescargaMasivaService.svc';

    private const string SOAP_ACTION_SOLICITAR = 'http://DescargaMasivaTerceros.sat.gob.mx/ISolicitaDescargaService/SolicitaDescarga';

    private const string SOAP_ACTION_VERIFICAR = 'http://DescargaMasivaTerceros.sat.gob.mx/IVerificaSolicitudDescargaService/VerificaSolicitudDescarga';

    private const string SOAP_ACTION_DESCARGAR = 'http://DescargaMasivaTerceros.sat.gob.mx/IDescargaMasivaTercerosService/Descargar';

    private const int TIMEOUT_SEC = 60;

    public function __construct(
        private readonly SatTokenLike $token,
        private readonly CredentialLike $credential,
    ) {
    }

    public function solicitar(SolicitudParams $params): SolicitudResult
    {
        $signed = $this->signComponents('SolicitudDescarga-' . $params->rfcSolicitante);
        $body = Solicitar::buildSolicitarRequest(
            $params,
            $this->token->value(),
            $signed['cert'],
            $signed['signatureValue'],
        );

        $xml = $this->post(self::URL_SOLICITAR, self::SOAP_ACTION_SOLICITAR, $body);

        return Solicitar::parseSolicitarResponse($xml);
    }

    public function verificar(string $idSolicitud): VerificacionResult
    {
        $rfc = $this->credential->rfc();
        $signed = $this->signComponents('VerificaSolicitud-' . $idSolicitud);
        $body = Verificar::buildVerificarRequest(
            $idSolicitud,
            $rfc,
            $this->token->value(),
            $signed['cert'],
            $signed['signatureValue'],
        );

        $xml = $this->post(self::URL_VERIFICAR, self::SOAP_ACTION_VERIFICAR, $body);

        return Verificar::parseVerificarResponse($xml);
    }

    public function descargar(string $idPaquete): string
    {
        $rfc = $this->credential->rfc();
        $signed = $this->signComponents('Descarga-' . $idPaquete);
        $body = Descargar::buildDescargarRequest(
            $idPaquete,
            $rfc,
            $this->token->value(),
            $signed['cert'],
            $signed['signatureValue'],
        );

        $xml = $this->post(self::URL_DESCARGAR, self::SOAP_ACTION_DESCARGAR, $body);

        return Descargar::parseDescargarResponse($xml);
    }

    /**
     * @return array{cert: string, signatureValue: string}
     */
    private function signComponents(string $content): array
    {
        $signatureValue = $this->credential->sign($content);
        $pemCert = $this->credential->certificate()->toPem();
        $cert = preg_replace('/\s+/', '', str_replace(
            ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'],
            '',
            $pemCert
        ));

        return [
            'cert' => $cert,
            'signatureValue' => $signatureValue,
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
                    'Timeout: el webservice del SAT no respondio en ' . self::TIMEOUT_SEC . ' segundos'
                );
            }
            throw new RuntimeException('Error de red al conectar con el SAT: ' . $msg);
        }

        $statusLine = $http_response_header[0] ?? '';
        $code = 0;
        if (preg_match('#HTTP/\S+\s+(\d+)#', $statusLine, $m)) {
            $code = (int) $m[1];
        }

        if ($code < 200 || $code >= 300) {
            $text = preg_match('#\s(.+)$#', $statusLine, $tm) ? trim($tm[1]) : '';
            throw new RuntimeException(
                'El webservice del SAT retorno HTTP ' . $code . ($text !== '' ? ': ' . $text : '')
            );
        }

        return $result;
    }
}
