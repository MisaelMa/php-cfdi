<?php

declare(strict_types=1);

namespace Sat\Scraper;

/**
 * Sesión del portal CFDI del SAT (cookies y metadatos).
 */
final class SesionSat
{
    /**
     * @param array<string, string> $cookies
     */
    public function __construct(
        public array $cookies,
        public ?string $csrfToken = null,
        public string $rfc = '',
        public bool $authenticated = false,
        public ?\DateTimeImmutable $expiresAt = null,
    ) {
    }
}

final readonly class ConsultaCfdiParams
{
    public function __construct(
        public string $fechaInicio,
        public string $fechaFin,
        public ?string $rfcEmisor = null,
        public ?string $rfcReceptor = null,
        public ?string $tipoComprobante = null,
        /** @var 'vigente'|'cancelado'|'todos'|null */
        public ?string $estadoCfdi = null,
    ) {
    }
}

final readonly class CfdiConsultaResult
{
    public function __construct(
        public string $uuid,
        public string $rfcEmisor,
        public string $nombreEmisor,
        public string $rfcReceptor,
        public string $nombreReceptor,
        public string $fechaEmision,
        public string $fechaCertificacion,
        public float $total,
        public string $efecto,
        public string $estado,
    ) {
    }
}

/**
 * Cliente HTTP contra el portal del SAT (autenticación CIEC/FIEL y consulta de CFDIs).
 */
final class SatPortal
{
    private const DEFAULT_BASE_URL = 'https://portalcfdi.facturaelectronica.sat.gob.mx';

    private const DEFAULT_TIMEOUT_MS = 30_000;

    private const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

    private readonly string $baseUrl;

    private readonly int $timeoutMs;

    private readonly string $userAgent;

    public function __construct(?ScraperConfig $config = null)
    {
        $config ??= new ScraperConfig();
        $this->baseUrl = $config->baseUrl ?? self::DEFAULT_BASE_URL;
        $this->timeoutMs = $config->timeoutMs ?? self::DEFAULT_TIMEOUT_MS;
        $this->userAgent = $config->userAgent ?? self::DEFAULT_USER_AGENT;
    }

    public function login(CredencialCiec|CredencialFiel $credencial): SesionSat
    {
        if ($credencial instanceof CredencialCiec) {
            return $this->loginCiec($credencial->rfc, $credencial->password);
        }

        return $this->loginFiel($credencial);
    }

    /**
     * @return list<CfdiConsultaResult>
     */
    public function consultarCfdis(SesionSat $sesion, ConsultaCfdiParams $params): array
    {
        $this->validateSesion($sesion);

        $url = rtrim($this->baseUrl, '/') . '/ConsultaEmisor.aspx';
        $form = [
            'ctl00$MainContent$TxtFechaInicial' => $params->fechaInicio,
            'ctl00$MainContent$TxtFechaFinal' => $params->fechaFin,
        ];
        if ($params->rfcReceptor !== null && $params->rfcReceptor !== '') {
            $form['ctl00$MainContent$TxtRfcReceptor'] = $params->rfcReceptor;
        }

        $html = $this->postForm($url, $form, $sesion);

        return $this->parseConsultaResults($html);
    }

    public function logout(SesionSat $sesion): void
    {
        $url = rtrim($this->baseUrl, '/') . '/logout.aspx';
        $this->get($url, $sesion);
        $sesion->authenticated = false;
    }

    private function loginCiec(string $rfc, string $password): SesionSat
    {
        $loginUrl = rtrim($this->baseUrl, '/')
            . '/nidp/wsfed/ep?id=SATUPCFDiCon&sid=0&option=credential&sid=0';
        $body = http_build_query([
            'Ecom_User_ID' => $rfc,
            'Ecom_Password' => $password,
            'option' => 'credential',
            'submit' => 'Enviar',
        ], '', '&', PHP_QUERY_RFC3986);

        [$status, , $cookies] = $this->fetchWithTimeout($loginUrl, 'POST', $body, null, true);

        $authenticated = $status === 302 || $status === 200;
        $expiresAt = (new \DateTimeImmutable())->modify('+30 minutes');

        return new SesionSat(
            cookies: $cookies,
            csrfToken: null,
            rfc: $rfc,
            authenticated: $authenticated,
            expiresAt: $expiresAt,
        );
    }

    private function loginFiel(CredencialFiel $credencial): SesionSat
    {
        $loginUrl = rtrim($this->baseUrl, '/') . '/nidp/wsfed/ep?id=SATx509Custom&sid=0&option=credential';
        $body = http_build_query([
            'credentialToken' => $credencial->certificatePem,
            'credentialSecret' => $credencial->privateKeyPem,
            'option' => 'credential',
        ], '', '&', PHP_QUERY_RFC3986);

        [$status, , $cookies] = $this->fetchWithTimeout($loginUrl, 'POST', $body, null, true);

        $authenticated = $status === 302 || $status === 200;
        $expiresAt = (new \DateTimeImmutable())->modify('+30 minutes');

        return new SesionSat(
            cookies: $cookies,
            csrfToken: null,
            rfc: '',
            authenticated: $authenticated,
            expiresAt: $expiresAt,
        );
    }

    private function validateSesion(SesionSat $sesion): void
    {
        if (! $sesion->authenticated) {
            throw new \RuntimeException('La sesion del SAT no esta activa');
        }
        if ($sesion->expiresAt !== null && $sesion->expiresAt < new \DateTimeImmutable()) {
            throw new \RuntimeException('La sesion del SAT ha expirado');
        }
    }

    private function get(string $url, SesionSat $sesion): string
    {
        [, $text] = $this->fetchWithTimeout($url, 'GET', null, $sesion, false);

        return $text;
    }

    /**
     * @param array<string, string> $form
     */
    private function postForm(string $url, array $form, SesionSat $sesion): string
    {
        $body = http_build_query($form, '', '&', PHP_QUERY_RFC3986);
        [, $text] = $this->fetchWithTimeout($url, 'POST', $body, $sesion, false);

        return $text;
    }

    /**
     * @return array{0: int, 1: string, 2: array<string, string>}
     */
    private function fetchWithTimeout(
        string $url,
        string $method,
        ?string $body,
        ?SesionSat $sesion,
        bool $noFollowRedirect,
    ): array {
        $timeoutSec = max(1, (int) ceil($this->timeoutMs / 1000));

        $headerLines = ['User-Agent: ' . $this->userAgent];
        if ($method === 'POST') {
            $headerLines[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        if ($sesion !== null && $sesion->cookies !== []) {
            $headerLines[] = 'Cookie: ' . $this->buildCookieHeader($sesion->cookies);
        }

        $httpOptions = [
            'method' => $method,
            'header' => implode("\r\n", $headerLines),
            'timeout' => $timeoutSec,
            'ignore_errors' => true,
        ];
        if ($body !== null) {
            $httpOptions['content'] = $body;
        }
        if ($noFollowRedirect) {
            $httpOptions['follow_location'] = 0;
        }

        $ctx = stream_context_create(['http' => $httpOptions]);

        $prevHandler = set_error_handler(static function (int $errno, string $errstr): bool {
            return true;
        });

        try {
            $responseBody = @file_get_contents($url, false, $ctx);
        } finally {
            if ($prevHandler !== null) {
                set_error_handler($prevHandler);
            } else {
                restore_error_handler();
            }
        }

        /** @var array<int, string> $responseHeaders */
        $responseHeaders = $http_response_header ?? [];
        $status = $this->parseStatusCode($responseHeaders);
        $cookies = self::extractCookiesFromHeaders($responseHeaders);

        if ($responseBody === false) {
            $msg = error_get_last()['message'] ?? 'desconocido';
            if (str_contains(strtolower($msg), 'timed out') || str_contains($msg, 'timed out')) {
                throw new \RuntimeException(
                    'Timeout: el portal del SAT no respondio en ' . ($this->timeoutMs / 1000) . ' segundos'
                );
            }

            throw new \RuntimeException(
                'Error de red al conectar con el portal del SAT: ' . $msg
            );
        }

        return [$status, $responseBody, $cookies];
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
     * @return array<string, string>
     */
    private static function extractCookiesFromHeaders(array $responseHeaders): array
    {
        $cookies = [];
        foreach ($responseHeaders as $line) {
            if (stripos($line, 'Set-Cookie:') !== 0) {
                continue;
            }
            $rest = trim(substr($line, strlen('Set-Cookie:')));
            $pair = explode(';', $rest, 2)[0];
            if (! str_contains($pair, '=')) {
                continue;
            }
            [$name, $value] = explode('=', $pair, 2);
            $name = trim($name);
            if ($name !== '') {
                $cookies[$name] = trim($value);
            }
        }

        return $cookies;
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

    /**
     * @return list<CfdiConsultaResult>
     */
    private function parseConsultaResults(string $html): array
    {
        $results = [];
        if (preg_match_all(
            '/<tr[^>]*class="[^"]*rgRow[^"]*"[^>]*>([\s\S]*?)<\/tr>/i',
            $html,
            $matches,
            PREG_SET_ORDER
        ) !== 0) {
            foreach ($matches as $match) {
                $cells = $this->extractCells($match[1]);
                if (count($cells) >= 9) {
                    $total = filter_var(str_replace(',', '', $cells[7]), FILTER_VALIDATE_FLOAT);

                    $results[] = new CfdiConsultaResult(
                        uuid: $cells[0],
                        rfcEmisor: $cells[1],
                        nombreEmisor: $cells[2],
                        rfcReceptor: $cells[3],
                        nombreReceptor: $cells[4],
                        fechaEmision: $cells[5],
                        fechaCertificacion: $cells[6],
                        total: $total !== false ? (float) $total : 0.0,
                        efecto: $cells[8],
                        estado: $cells[9] ?? 'Vigente',
                    );
                }
            }
        }

        return $results;
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
