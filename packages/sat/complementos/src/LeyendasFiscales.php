<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class LeyendasFiscales extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/leyendasFiscales';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/leyendasFiscales/leyendasFisc.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('leyendasFisc:LeyendasFiscales', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => array_merge(['version' => '1.0'], $attributes),
        ];
    }
}
