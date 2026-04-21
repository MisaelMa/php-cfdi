<?php

declare(strict_types=1);

namespace Sat\Pacs;

/**
 * Configuración común para integrar un PAC (Proveedor Autorizado de Certificación).
 *
 * Propiedades: URL base u origen (opcional), usuario, contraseña, y modo sandbox para endpoints de demostración.
 */
final readonly class PacConfig
{
    public function __construct(
        public ?string $url,
        public string $user,
        public string $password,
        public bool $sandbox = false,
    ) {
    }
}
