<?php

namespace Cfdi\Descarga;

use DateTimeInterface;

interface SatTokenLike
{
    public function value(): string;

    public function created(): DateTimeInterface;

    public function expires(): DateTimeInterface;
}
