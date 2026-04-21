<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Donat extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/donat';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/donat/donat11.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('donat:Donatarias', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
