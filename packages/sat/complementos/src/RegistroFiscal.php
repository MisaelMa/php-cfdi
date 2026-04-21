<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class RegistroFiscal extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/registrofiscal';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/cfdiregistrofiscal/cfdiregistrofiscal.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('registrofiscal:CFDIRegistroFiscal', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => array_merge(['Folio' => '', 'Version' => '1.0'], $attributes),
        ];
    }
}
