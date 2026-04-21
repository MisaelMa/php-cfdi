<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Destruccion extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/certificadodestruccion';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/certificadodestruccion/certificadodedestruccion.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('destruccion:certificadodedestruccion', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
