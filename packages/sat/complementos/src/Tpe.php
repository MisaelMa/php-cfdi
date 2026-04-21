<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Tpe extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/TuristaPasajeroExtranjero';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/TuristaPasajeroExtranjero/TuristaPasajeroExtranjero.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('tpe:TuristaPasajeroExtranjero', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
