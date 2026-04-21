<?php

declare(strict_types=1);

namespace Sat\Scraper;

final readonly class ScraperConfig
{
    public function __construct(
        /** Tiempo máximo de espera por petición (ms) */
        public ?int $timeoutMs = null,
        public ?string $userAgent = null,
        /** URL base del portal (p. ej. pruebas) */
        public ?string $baseUrl = null,
    ) {
    }
}
