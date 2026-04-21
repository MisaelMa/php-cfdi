<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Pagos20 extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/Pagos20';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('pago20:Pagos', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => array_merge(['Version' => '2.0'], $attributes),
        ];
    }
}
