<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class CartaPorte30 extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/CartaPorte30';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/CartaPorte/CartaPorte30.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('cartaporte30:CartaPorte', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
