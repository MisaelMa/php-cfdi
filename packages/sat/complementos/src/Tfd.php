<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Tfd extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/TimbreFiscalDigital';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('tfd:TimbreFiscalDigital', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
