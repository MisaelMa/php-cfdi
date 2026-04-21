<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class Decreto extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/renovacionysustitucionvehiculos';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/renovacionysustitucionvehiculos/renovacionysustitucionvehiculos.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('decreto:renovacionysustitucionvehiculos', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
