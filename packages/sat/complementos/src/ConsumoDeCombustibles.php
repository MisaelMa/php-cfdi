<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class ConsumoDeCombustibles extends Complemento
{
    private const XMLNS = 'http://www.sat.gob.mx/ConsumoDeCombustibles11';

    private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/ConsumoDeCombustibles/consumodeCombustibles11.xsd';

    public function __construct(array $attributes)
    {
        parent::__construct('consumodecombustibles11:ConsumoDeCombustibles', self::XMLNS, self::XSD);
        $this->complemento = [
            '_attributes' => $attributes,
        ];
    }
}
