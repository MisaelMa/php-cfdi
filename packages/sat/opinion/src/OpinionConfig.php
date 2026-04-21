<?php

declare(strict_types=1);

namespace Sat\Opinion;

final readonly class OpinionConfig
{
    public function __construct(
        /** Tiempo máximo de espera por petición (ms) */
        public ?int $timeoutMs = null,
        /** URL base del portal (p. ej. pruebas) */
        public ?string $baseUrl = null,
    ) {
    }
}
