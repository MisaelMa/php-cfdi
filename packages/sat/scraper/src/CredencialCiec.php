<?php

declare(strict_types=1);

namespace Sat\Scraper;

final readonly class CredencialCiec
{
    public function __construct(
        public string $rfc,
        public string $password,
        public TipoAutenticacion $tipo = TipoAutenticacion::Ciec,
    ) {
    }
}
