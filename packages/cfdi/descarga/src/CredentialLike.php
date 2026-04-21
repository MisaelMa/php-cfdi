<?php

namespace Cfdi\Descarga;

interface CertificateLike
{
    public function toDer(): string;

    public function toPem(): string;
}

interface CredentialLike
{
    public function certificate(): CertificateLike;

    public function sign(string $data): string;

    public function rfc(): string;
}
