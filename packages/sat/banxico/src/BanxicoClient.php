<?php

declare(strict_types=1);

namespace Sat\Banxico;

final class BanxicoClient
{
    private const BASE_URL = 'https://www.banxico.org.mx/SieAPIRest/service/v1/series';

    private const DEFAULT_TIMEOUT_SECONDS = 30;

    /** @var array<non-empty-string, non-empty-string> Series SIE (FIX u homologables): pesos por unidad de moneda extranjera. */
    private const SERIE_BANXICO = [
        'USD' => 'SF43718',
        'EUR' => 'SF46410',
        'GBP' => 'SF46407',
        'JPY' => 'SF46406',
        'CAD' => 'SF60632',
    ];

    private readonly string $token;

    public function __construct(string $token)
    {
        if (trim($token) === '') {
            throw new \InvalidArgumentException('El token de Banxico es obligatorio');
        }
        $this->token = trim($token);
    }

    /**
     * @return list<TipoCambio>
     */
    public function obtenerTipoCambio(Moneda $moneda, \DateTimeInterface $fechaInicio, \DateTimeInterface $fechaFin): array
    {
        $inicio = $fechaInicio->format('Y-m-d');
        $fin = $fechaFin->format('Y-m-d');
        if ($fin < $inicio) {
            throw new \InvalidArgumentException('fechaFin debe ser mayor o igual a fechaInicio');
        }

        $serie = self::resolveSerie($moneda);
        $url = $this->buildUrl("{$serie}/datos/{$inicio}/{$fin}");
        $json = $this->fetchJson($url);

        return self::parseSerieObservacionesRango($moneda, $json);
    }

    public function obtenerTipoCambioActual(Moneda $moneda): ?TipoCambio
    {
        $serie = self::resolveSerie($moneda);
        $url = $this->buildUrl("{$serie}/datos/oportuno");

        try {
            $json = $this->fetchJson($url);

            return self::parseUltimoTipoCambio($moneda, $json);
        } catch (\RuntimeException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'N/E') || str_contains($msg, 'sin observaciones')) {
                return null;
            }

            throw $e;
        }
    }

    private function buildUrl(string $pathSuffix): string
    {
        $base = rtrim(self::BASE_URL, '/') . '/' . ltrim($pathSuffix, '/');
        $sep = str_contains($base, '?') ? '&' : '?';

        return $base . $sep . 'token=' . rawurlencode($this->token);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchJson(string $url): array
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => self::DEFAULT_TIMEOUT_SECONDS,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\n",
            ],
        ]);

        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            throw new \RuntimeException('Error de red al consultar Banxico');
        }

        $responseHeaders = $http_response_header ?? [];
        $statusLine = $responseHeaders[0] ?? '';
        if (preg_match('#HTTP/\S+\s+(\d+)#', $statusLine, $m) && (int) $m[1] !== 200) {
            throw new \RuntimeException("Banxico HTTP {$m[1]}");
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Respuesta Banxico no es JSON válido', 0, $e);
        }

        if (! is_array($decoded)) {
            throw new \RuntimeException('Respuesta Banxico no es JSON válido');
        }

        return $decoded;
    }

    private static function resolveSerie(Moneda $moneda): string
    {
        $id = self::SERIE_BANXICO[$moneda->value] ?? null;
        if ($id === null || $id === '') {
            throw new \InvalidArgumentException("No hay serie Banxico configurada para la moneda: {$moneda->value}");
        }

        return $id;
    }

    /**
     * @param array<string, mixed> $json
     */
    private static function parseUltimoTipoCambio(Moneda $moneda, array $json): TipoCambio
    {
        /** @var mixed $series */
        $series = $json['bmx']['series'][0] ?? null;
        if (! is_array($series)) {
            throw new \RuntimeException('Respuesta Banxico sin observaciones en la serie solicitada');
        }

        /** @var mixed $datos */
        $datos = $series['datos'] ?? null;
        if (! is_array($datos) || $datos === []) {
            throw new \RuntimeException('Respuesta Banxico sin observaciones en la serie solicitada');
        }

        $ultimo = $datos[array_key_last($datos)];
        if (! is_array($ultimo)) {
            throw new \RuntimeException('Respuesta Banxico sin observaciones en la serie solicitada');
        }

        return self::parseDatoEstricto($moneda, $ultimo);
    }

    /**
     * @param array<string, mixed> $json
     * @return list<TipoCambio>
     */
    private static function parseSerieObservacionesRango(Moneda $moneda, array $json): array
    {
        /** @var mixed $series */
        $series = $json['bmx']['series'][0] ?? null;
        if (! is_array($series)) {
            throw new \RuntimeException('Respuesta Banxico sin observaciones en la serie solicitada');
        }

        /** @var mixed $datos */
        $datos = $series['datos'] ?? null;
        if (! is_array($datos) || $datos === []) {
            throw new \RuntimeException('Respuesta Banxico sin observaciones en la serie solicitada');
        }

        $out = [];
        foreach ($datos as $row) {
            if (! is_array($row)) {
                continue;
            }
            $tc = self::parseDato($moneda, $row);
            if ($tc !== null) {
                $out[] = $tc;
            }
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function parseDatoEstricto(Moneda $moneda, array $row): TipoCambio
    {
        $fechaRaw = $row['fecha'] ?? null;
        $datoRaw = $row['dato'] ?? null;
        if (! is_string($fechaRaw) || trim($fechaRaw) === '' || ! is_string($datoRaw) || $datoRaw === '') {
            throw new \RuntimeException('Respuesta Banxico sin observaciones en la serie solicitada');
        }
        if (trim($datoRaw) === 'N/E') {
            throw new \RuntimeException('Banxico reportó dato no disponible (N/E) para la fecha o serie');
        }

        $normalized = str_replace(',', '', $datoRaw);
        $valor = filter_var($normalized, FILTER_VALIDATE_FLOAT);
        if ($valor === false) {
            throw new \RuntimeException("Valor de tipo de cambio inválido: {$datoRaw}");
        }

        return new TipoCambio(fecha: trim($fechaRaw), valor: (float) $valor, moneda: $moneda);
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function parseDato(Moneda $moneda, array $row): ?TipoCambio
    {
        $fechaRaw = $row['fecha'] ?? null;
        $datoRaw = $row['dato'] ?? null;
        if (! is_string($fechaRaw) || trim($fechaRaw) === '') {
            return null;
        }
        if (! is_string($datoRaw) || $datoRaw === '') {
            return null;
        }
        if (trim($datoRaw) === 'N/E') {
            return null;
        }

        $normalized = str_replace(',', '', $datoRaw);
        $valor = filter_var($normalized, FILTER_VALIDATE_FLOAT);
        if ($valor === false) {
            throw new \RuntimeException("Valor de tipo de cambio inválido: {$datoRaw}");
        }

        return new TipoCambio(fecha: trim($fechaRaw), valor: (float) $valor, moneda: $moneda);
    }
}
