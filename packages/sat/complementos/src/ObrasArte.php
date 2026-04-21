<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class ObrasArte extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/arteantiguedades';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/arteantiguedades/obrasarteantiguedades.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('obrasarte:obrasarteantiguedades', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
