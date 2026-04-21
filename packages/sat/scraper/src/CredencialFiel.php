<?php

declare(strict_types=1);

namespace Sat\Scraper;

final readonly class CredencialFiel
{
    public function __construct(
        public string $certificatePem,
        public string $privateKeyPem,
        public string $password,
        public TipoAutenticacion $tipo = TipoAutenticacion::Fiel,
    ) {
    }
}
