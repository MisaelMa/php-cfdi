<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class Gceh extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/GastosHidrocarburos10';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/GastosHidrocarburos10/GastosHidrocarburos10.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('gceh:GastosHidrocarburos', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
