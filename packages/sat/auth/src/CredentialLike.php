<?php

declare(strict_types=1);

namespace Sat\Auth;

interface CredentialLike
{
    public function sign(string $data): string;

    public function getCertificatePem(): string;

    public function getRfc(): string;
}
