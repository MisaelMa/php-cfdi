<?php

namespace Cfdi\Estado;

class ConsultarEstado
{
    private const TIMEOUT_SECONDS = 30;

    /**
     * Consulta el estado de un CFDI en el webservice del SAT.
     *
     * @throws \RuntimeException Si hay problema de red, timeout o respuesta SOAP invalida.
     */
    public static function consultar(ConsultaParams $params): ConsultaResult
    {
        $body = Soap::buildSoapRequest($params);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: ' . Soap::SOAP_ACTION,
                ]),
                'content' => $body,
                'timeout' => self::TIMEOUT_SECONDS,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents(Soap::WEBSERVICE_URL, false, $context);

        if ($response === false) {
            throw new \RuntimeException('Error de red al consultar el estado del CFDI');
        }

        $statusLine = $http_response_header[0] ?? '';
        if (preg_match('/HTTP\/\S+\s+(\d+)/', $statusLine, $m)) {
            $status = (int) $m[1];
            if ($status < 200 || $status >= 300) {
                throw new \RuntimeException("El webservice del SAT retorno HTTP {$status}");
            }
        }

        return Soap::parseSoapResponse($response);
    }
}
