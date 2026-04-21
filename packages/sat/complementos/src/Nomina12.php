<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Nomina12 extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/nomina12';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina12.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('nomina12:Nomina', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
