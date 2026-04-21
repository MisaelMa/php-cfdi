<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class CartaPorte20 extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/CartaPorte20';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/CartaPorte/CartaPorte20.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('cartaporte20:CartaPorte', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
