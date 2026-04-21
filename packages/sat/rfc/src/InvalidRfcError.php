<?php

namespace Cfdi\Rfc;

class InvalidRfcError extends \InvalidArgumentException
{
    public function __construct(string $rfc)
    {
        parent::__construct("'{$rfc}' is not a valid RFC");
    }
}
