<?php

declare(strict_types=1);

namespace Sat\Captcha;

interface CaptchaSolver
{
    public function solve(CaptchaChallenge $challenge): CaptchaResult;
}
