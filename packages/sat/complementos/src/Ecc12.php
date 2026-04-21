<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class Ecc12 extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/EstadoDeCuentaCombustible12';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/EstadoDeCuentaCombustible/ecc12.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('ecc12:EstadoDeCuentaCombustible', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
