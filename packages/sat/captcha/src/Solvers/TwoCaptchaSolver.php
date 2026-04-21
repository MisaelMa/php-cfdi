<?php

declare(strict_types=1);

namespace Sat\Captcha\Solvers;

use InvalidArgumentException;
use RuntimeException;
use Sat\Captcha\CaptchaChallenge;
use Sat\Captcha\CaptchaResult;
use Sat\Captcha\CaptchaSolver;

/**
 * Resolutor vía API 2captcha.com (envío base64 a in.php, sondeo res.php).
 *
 * Los cierres opcionales permiten inyectar HTTP en pruebas: ($url, $params) => string y ($url) => string.
 */
final class TwoCaptchaSolver implements CaptchaSolver
{
    private const string API_URL = 'https://2captcha.com/in.php';

    private const string RESULT_URL = 'https://2captcha.com/res.php';

    private const int POLL_INTERVAL_MS = 5_000;

    public function __construct(
        private readonly string $apiKey,
        private readonly int $timeoutMs = 120_000,
        private readonly ?\Closure $postForm = null,
        private readonly ?\Closure $getUrl = null,
        private readonly int $pollIntervalMs = self::POLL_INTERVAL_MS,
    ) {
    }

    public function solve(CaptchaChallenge $challenge): CaptchaResult
    {
        if ($challenge->type === 'userrecaptcha') {
            throw new InvalidArgumentException(
                'userrecaptcha requiere googlekey y pageurl; este modelo solo soporta imagen base64 (type=base64).'
            );
        }

        if ($challenge->image === '') {
            throw new InvalidArgumentException(
                'Se requiere image (base64) para resolver el captcha con 2captcha.'
            );
        }

        $taskId = $this->submitTask($challenge);

        return $this->waitForResult($taskId);
    }

    public function report(string $taskId, bool $correct): void
    {
        $action = $correct ? 'reportgood' : 'reportbad';
        $url = sprintf(
            '%s?key=%s&action=%s&id=%s',
            self::RESULT_URL,
            rawurlencode($this->apiKey),
            $action,
            rawurlencode($taskId)
        );
        $this->httpGet($url);
    }

    private function submitTask(CaptchaChallenge $challenge): string
    {
        $params = [
            'key' => $this->apiKey,
            'json' => '1',
            'method' => $challenge->type,
            'body' => $challenge->image,
        ];

        $raw = $this->httpPostForm(self::API_URL, $params);
        $data = $this->decodeJsonResponse($raw);

        if (($data['status'] ?? 0) !== 1) {
            $msg = (string) ($data['request'] ?? $raw);
            throw new RuntimeException('Error al enviar captcha a 2captcha: ' . $msg);
        }

        $request = (string) $data['request'];
        if ($request === '') {
            throw new RuntimeException('Error al enviar captcha a 2captcha: respuesta vacía');
        }

        return $request;
    }

    private function waitForResult(string $taskId): CaptchaResult
    {
        $startMs = (int) (microtime(true) * 1000);

        while ((int) (microtime(true) * 1000) - $startMs < $this->timeoutMs) {
            usleep(max(0, $this->pollIntervalMs) * 1000);

            $url = sprintf(
                '%s?key=%s&action=get&id=%s&json=1',
                self::RESULT_URL,
                rawurlencode($this->apiKey),
                rawurlencode($taskId)
            );
            $raw = $this->httpGet($url);
            $data = $this->decodeJsonResponse($raw);

            if (($data['status'] ?? 0) === 1) {
                return new CaptchaResult(text: (string) $data['request'], id: $taskId);
            }

            $request = (string) ($data['request'] ?? '');
            if ($request !== 'CAPCHA_NOT_READY') {
                throw new RuntimeException('Error de 2captcha: ' . $request);
            }
        }

        throw new RuntimeException(
            sprintf('Timeout: 2captcha no resolvio el captcha en %d segundos', (int) ($this->timeoutMs / 1000))
        );
    }

    /**
     * @param array<string, string> $params
     */
    private function httpPostForm(string $url, array $params): string
    {
        if ($this->postForm !== null) {
            return $this->postForm->__invoke($url, $params);
        }

        $body = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $body,
                'timeout' => max(1, (int) ceil($this->timeoutMs / 1000)),
            ],
        ]);

        $result = @file_get_contents($url, false, $ctx);
        if ($result === false) {
            throw new RuntimeException('Fallo HTTP al enviar captcha a 2captcha');
        }

        return $result;
    }

    private function httpGet(string $url): string
    {
        if ($this->getUrl !== null) {
            return $this->getUrl->__invoke($url);
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => max(1, (int) ceil($this->timeoutMs / 1000)),
            ],
        ]);

        $result = @file_get_contents($url, false, $ctx);
        if ($result === false) {
            throw new RuntimeException('Fallo HTTP al consultar resultado en 2captcha');
        }

        return $result;
    }

    /**
     * @return array{status?: int, request?: string}
     */
    private function decodeJsonResponse(string $raw): array
    {
        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new RuntimeException('Respuesta JSON inválida de 2captcha: ' . $raw);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Respuesta JSON inválida de 2captcha: ' . $raw);
        }

        /** @var array{status?: int, request?: string} $decoded */
        return $decoded;
    }
}
