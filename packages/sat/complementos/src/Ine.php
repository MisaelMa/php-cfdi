<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Ine extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/ine';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/ine/ine11.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('ine:INE', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
