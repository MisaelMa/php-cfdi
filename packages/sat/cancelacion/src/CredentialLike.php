<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

interface CredentialLike
{
    public function certificate(): CertificateLike;

    public function sign(string $data): string;

    public function rfc(): string;
}
