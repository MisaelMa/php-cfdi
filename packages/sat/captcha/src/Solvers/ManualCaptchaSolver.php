<?php

declare(strict_types=1);

namespace Sat\Captcha\Solvers;

use RuntimeException;
use Sat\Captcha\CaptchaChallenge;
use Sat\Captcha\CaptchaResult;
use Sat\Captcha\CaptchaSolver;

/**
 * Resolución manual: el closure recibe el reto y debe devolver el texto ingresado por el usuario.
 */
final class ManualCaptchaSolver implements CaptchaSolver
{
    public function __construct(
        private readonly \Closure $prompt,
    ) {
    }

    public function solve(CaptchaChallenge $challenge): CaptchaResult
    {
        $text = ($this->prompt)($challenge);
        if ($text === '') {
            throw new RuntimeException('No se proporciono respuesta al captcha');
        }

        return new CaptchaResult(text: $text);
    }
}
