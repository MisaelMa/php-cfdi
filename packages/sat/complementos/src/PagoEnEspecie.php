<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class PagoEnEspecie extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/pagoenespecie';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/pagoenespecie/pagoenespecie.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('pagoenespecie:PagoEnEspecie', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
