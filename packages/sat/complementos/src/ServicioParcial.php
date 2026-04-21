<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class ServicioParcial extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/servicioparcialconstruccion';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/servicioparcialconstruccion/servicioparcialconstruccion.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('servicioparcial:parcialesconstruccion', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
