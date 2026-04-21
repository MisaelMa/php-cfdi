<?php

declare(strict_types=1);

namespace Sat\Captcha;

/**
 * Datos del captcha a resolver (imagen en base64 y método para 2captcha, p. ej. "base64").
 */
readonly class CaptchaChallenge
{
    public function __construct(
        public string $image,
        public string $type = 'base64',
    ) {
    }
}
