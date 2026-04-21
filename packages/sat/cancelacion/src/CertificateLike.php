<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

interface CertificateLike
{
    public function toPem(): string;

    public function serialNumber(): string;
}
