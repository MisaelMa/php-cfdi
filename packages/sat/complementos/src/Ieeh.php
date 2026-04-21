<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class Ieeh extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/IngresosHidrocarburos10';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/IngresosHidrocarburos10/IngresosHidrocarburos.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('ieeh:IngresosHidrocarburos', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
