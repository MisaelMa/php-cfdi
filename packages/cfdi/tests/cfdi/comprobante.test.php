<?php

use Sat\Cfdi\Comprobante;

test('example', function () {
    $comprobante = new Comprobante();

    $comprobante->addSchemaLocation([
        'http://www.sat.gob.mx/cfd/4',
        'http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd',
    ]);

    $comprobante->addSchemaLocation([
        'http://www.sat.gob.mx/cfd/4',
        'http://www.sat.gob.mx/cfd/4',
        'http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd',
        'http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd',
        'http://www.sat.gob.mx/iedu',
    ]);

    expect($comprobante->toXml())->toBe([
        'cfdi:Comprobante' => [
            '_attributes' => [
                'xsi:schemaLocation' => 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/iedu',
            ],
        ],
    ]);
});
