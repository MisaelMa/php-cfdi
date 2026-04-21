<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Pfic extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/pfic';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/pfic/pfic.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('pfic:PFintegranteCoordinado', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
