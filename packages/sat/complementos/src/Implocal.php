<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Implocal extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/implocal';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('implocal:ImpuestosLocales', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
