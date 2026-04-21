<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class VentaVehiculos extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/ventavehiculos';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/ventavehiculos/ventavehiculos11.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('ventavehiculos:VentaVehiculos', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
