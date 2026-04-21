<?php

declare(strict_types=1);

namespace Sat\Captcha;

/**
 * Texto resuelto y, si aplica, id de tarea del proveedor (p. ej. para reportar aciertos/errores).
 */
readonly class CaptchaResult
{
    public function __construct(
        public string $text,
        public ?string $id = null,
    ) {
    }
}
