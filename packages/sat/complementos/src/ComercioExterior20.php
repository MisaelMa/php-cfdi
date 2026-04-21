<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class ComercioExterior20 extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/ComercioExterior20';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/ComercioExterior20/ComercioExterior20.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('cce20:ComercioExterior', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
