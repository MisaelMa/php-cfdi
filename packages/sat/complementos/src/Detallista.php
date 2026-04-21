<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class Detallista extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/detallista';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/detallista/detallista.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('detallista:detallista', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
