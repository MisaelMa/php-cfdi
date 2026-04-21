<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class Spei extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/spei';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/spei/spei.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('spei:Complemento_SPEI', self::XMLNS, self::XSD);
        $this->complemento = $attributes === [] ? [] : ['_attributes' => $attributes];
    }
}
