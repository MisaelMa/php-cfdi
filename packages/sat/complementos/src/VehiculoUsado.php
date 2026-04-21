<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class VehiculoUsado extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/vehiculousado';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/vehiculousado/vehiculousado.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('vehiculousado:VehiculoUsado', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
