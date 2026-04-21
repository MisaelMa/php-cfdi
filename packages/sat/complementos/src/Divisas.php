<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Divisas extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/divisas';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/divisas/divisas.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('divisas:Divisas', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
