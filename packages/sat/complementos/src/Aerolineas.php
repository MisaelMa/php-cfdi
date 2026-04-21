<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Aerolineas extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/aerolineas';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/aerolineas/aerolineas.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('aerolineas:Aerolineas', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
