<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

interface SatTokenLike
{
    public function value(): string;
}
