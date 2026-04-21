<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class ValesDeDespensa extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/valesdedespensa';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/valesdedespensa/valesdedespensa.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('valesdedespensa:ValesDeDespensa', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
