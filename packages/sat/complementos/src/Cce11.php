<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Cce11 extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/ComercioExterior11';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/ComercioExterior11/ComercioExterior11.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('cce11:ComercioExterior', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
