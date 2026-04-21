<?php

declare(strict_types=1);

namespace Sat\Diot;

final readonly class DiotDeclaracion
{
    /**
     * @param list<OperacionTercero> $operaciones
     */
    public function __construct(
        public string $rfc,
        public int $ejercicio,
        public int $periodo,
        public array $operaciones,
    ) {
    }
}
