<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

final class NotariosPublicos extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/notariospublicos';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/notariospublicos/notariospublicos.xsd';

    public function __construct(array $attributes = [])
    {
        parent::__construct('notariospublicos:NotariosPublicos', self::XMLNS, self::XSD);
        $attrs = $attributes === [] ? ['Version' => '1.0'] : $attributes;
        $this->complemento = ['_attributes' => $attrs];
    }
}
