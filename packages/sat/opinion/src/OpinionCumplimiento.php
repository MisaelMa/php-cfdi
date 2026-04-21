<?php

declare(strict_types=1);

namespace Sat\Opinion;

/**
 * Sesión del portal SAT compatible con {@see OpinionCumplimiento} (misma forma que sat/scraper).
 */
final readonly class SesionPortal
{
    /**
     * @param array<string, string> $cookies
     */
    public function __construct(
        public array $cookies,
        public string $rfc,
        public bool $authenticated,
    ) {
    }
}

/**
 * Datos parseados de la opinión de cumplimiento.
 */
final readonly class OpinionCumplimientoDatos
{
    /**
     * @param list<ObligacionFiscal> $obligaciones
     */
    public function __construct(
        public string $rfc,
        public string $nombreContribuyente,
        public ResultadoOpinion $resultado,
        public string $fechaEmision,
        public string $folioOpinion,
        public array $obligaciones,
        public ?string $urlPdf = null,
    ) {
    }
}

/**
 * Cliente para obtener la opinión de cumplimiento (32-D) del SAT vía HTTP.
 *
 * Requiere sesión activa en el portal (p. ej. {@see \Sat\Scraper\SatPortal::login}).
 */
final class OpinionCumplimiento
{
    private const DEFAULT_BASE_URL = 'https://portalcfdi.facturaelectronica.sat.gob.mx';

    private const DEFAULT_TIMEOUT_MS = 30_000;

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

    private readonly string $baseUrl;

    private readonly int $timeoutMs;

    public function __construct(?OpinionConfig $config = null)
    {
        $config ??= new OpinionConfig();
        $this->baseUrl = $config->baseUrl ?? self::DEFAULT_BASE_URL;
        $this->timeoutMs = $config->timeoutMs ?? self::DEFAULT_TIMEOUT_MS;
    }

    public function obtener(SesionPortal $sesion): OpinionCumplimientoDatos
    {
        if (! $sesion->authenticated) {
            throw new \RuntimeException('Se requiere una sesion activa en el portal del SAT');
        }

        $url = rtrim($this->baseUrl, '/') . '/RecuperaOpinionCumplimiento.aspx';
        $html = $this->fetch($url, $sesion);

        return $this->parseOpinion($html, $sesion->rfc);
    }

    public function descargarPdf(SesionPortal $sesion, ?string $urlPdf = null): string
    {
        if (! $sesion->authenticated) {
            throw new \RuntimeException('Se requiere una sesion activa en el portal del SAT');
        }

        $url = $urlPdf ?? (rtrim($this->baseUrl, '/') . '/RecuperaOpinionCumplimiento.aspx?generar=1');
        [$status, $body] = $this->fetchRaw($url, $sesion);

        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException("Error descargando PDF: HTTP {$status}");
        }

        return $body;
    }

    private function fetch(string $url, SesionPortal $sesion): string
    {
        [, $body] = $this->fetchRaw($url, $sesion);

        return $body;
    }

    /**
     * @return array{0: int, 1: string} status, body
     */
    private function fetchRaw(string $url, SesionPortal $sesion): array
    {
        $timeoutSec = max(1, (int) ceil($this->timeoutMs / 1000));

        $headers = [
            'Cookie: ' . $this->buildCookieHeader($sesion->cookies),
            'User-Agent: ' . self::USER_AGENT,
        ];

        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => $timeoutSec,
                'ignore_errors' => true,
            ],
        ]);

        $prevHandler = set_error_handler(static function (int $errno, string $errstr): bool {
            return true;
        });

        try {
            $body = @file_get_contents($url, false, $ctx);
        } finally {
            if ($prevHandler !== null) {
                set_error_handler($prevHandler);
            } else {
                restore_error_handler();
            }
        }

        /** @var array<int, string> $responseHeaders */
        $responseHeaders = $http_response_header ?? [];

        if ($body === false) {
            $msg = error_get_last()['message'] ?? 'desconocido';
            if (str_contains(strtolower($msg), 'timed out') || str_contains($msg, 'timed out')) {
                throw new \RuntimeException(
                    'Timeout: el portal del SAT no respondio en ' . ($this->timeoutMs / 1000) . ' segundos'
                );
            }

            throw new \RuntimeException("Error de red: {$msg}");
        }

        return [$this->parseStatusCode($responseHeaders), $body];
    }

    /**
     * @param array<string, string> $cookies
     */
    private function buildCookieHeader(array $cookies): string
    {
        $parts = [];
        foreach ($cookies as $name => $value) {
            $parts[] = $name . '=' . $value;
        }

        return implode('; ', $parts);
    }

    /**
     * @param array<int, string> $responseHeaders
     */
    private function parseStatusCode(array $responseHeaders): int
    {
        $line = $responseHeaders[0] ?? '';
        if (preg_match('#HTTP/\S+\s+(\d+)#', $line, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    private function parseOpinion(string $html, string $rfc): OpinionCumplimientoDatos
    {
        $resultado = $this->extractResultado($html);
        $nombre = $this->extractBetween($html, 'Nombre, denominación o razón social:', '</span>')
            ?: $this->extractBetween($html, 'Nombre:', '</span>');
        $folio = $this->extractBetween($html, 'Folio:', '</span>')
            ?: $this->extractBetween($html, 'No. Operación:', '</span>');
        $fecha = $this->extractBetween($html, 'Fecha de emisión:', '</span>')
            ?: $this->extractBetween($html, 'Fecha:', '</span>');

        return new OpinionCumplimientoDatos(
            rfc: $rfc,
            nombreContribuyente: trim($nombre),
            resultado: $resultado,
            fechaEmision: trim($fecha),
            folioOpinion: trim($folio),
            obligaciones: $this->parseObligaciones($html),
        );
    }

    private function extractResultado(string $html): ResultadoOpinion
    {
        $lower = strtolower($html);
        if (str_contains($lower, 'positiv')) {
            return ResultadoOpinion::Positivo;
        }
        if (str_contains($lower, 'negativ')) {
            return ResultadoOpinion::Negativo;
        }
        if (str_contains($lower, 'suspenso')) {
            return ResultadoOpinion::EnSuspenso;
        }
        if (str_contains($lower, 'sin obligaciones')) {
            return ResultadoOpinion::InscritoSinObligaciones;
        }

        return ResultadoOpinion::NoInscrito;
    }

    private function extractBetween(string $html, string $start, string $end): string
    {
        $startIdx = strpos($html, $start);
        if ($startIdx === false) {
            return '';
        }
        $afterStart = $startIdx + strlen($start);
        $endIdx = strpos($html, $end, $afterStart);
        if ($endIdx === false) {
            return '';
        }

        return trim(strip_tags(substr($html, $afterStart, $endIdx - $afterStart)));
    }

    /**
     * @return list<ObligacionFiscal>
     */
    private function parseObligaciones(string $html): array
    {
        $obligaciones = [];
        $inObligaciones = false;

        if (preg_match_all('/<tr[^>]*>([\s\S]*?)<\/tr>/i', $html, $rowMatches, PREG_SET_ORDER) !== 0) {
            foreach ($rowMatches as $rowMatch) {
                $inner = $rowMatch[1];
                if (str_contains($inner, 'Obligaciones')) {
                    $inObligaciones = true;

                    continue;
                }
                if (! $inObligaciones) {
                    continue;
                }

                $cells = $this->extractCells($inner);
                if (count($cells) >= 3) {
                    $obligaciones[] = new ObligacionFiscal(
                        descripcion: $cells[0],
                        fechaInicio: $cells[1],
                        fechaFin: $cells[2] !== '' ? $cells[2] : null,
                        estado: $cells[3] ?? 'Activa',
                    );
                }
            }
        }

        return $obligaciones;
    }

    /**
     * @return list<string>
     */
    private function extractCells(string $rowHtml): array
    {
        $cells = [];
        if (preg_match_all('/<td[^>]*>([\s\S]*?)<\/td>/i', $rowHtml, $m) !== 0) {
            foreach ($m[1] as $raw) {
                $cells[] = trim(strip_tags($raw));
            }
        }

        return $cells;
    }
}
