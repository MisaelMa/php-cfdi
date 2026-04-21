<?php

use Sat\Cfdi\Concepto;

test('concepto constructor stores attributes', function () {
    $concepto = new Concepto([
        'ClaveProdServ' => '01010101',
        'NoIdentificacion' => 'UT421511',
        'Cantidad' => 1,
        'ClaveUnidad' => 'ACT',
        'Descripcion' => 'Venta',
        'ValorUnitario' => 130,
        'Importe' => 130,
        'Descuento' => 0,
        'ObjetoImp' => '02',
    ]);

    $result = $concepto->getConcept();
    expect($result['_attributes']['ClaveProdServ'])->toBe('01010101');
    expect($result['_attributes']['Cantidad'])->toBe(1);
    expect($result['_attributes']['Descripcion'])->toBe('Venta');
    expect($result['_attributes']['ValorUnitario'])->toBe(130);
    expect($result['_attributes']['Descuento'])->toBe(0);
});

test('concepto terceros adds ACuentaTerceros', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101']);
    $concepto->terceros([
        'RfcACuentaTerceros' => 'AAA010101AAA',
        'NombreACuentaTerceros' => 'Test',
        'RegimenFiscalACuentaTerceros' => '601',
        'DomicilioFiscalACuentaTerceros' => '86991',
    ]);

    $result = $concepto->getConcept();
    expect($result)->toHaveKey('cfdi:ACuentaTerceros');
    expect($result['cfdi:ACuentaTerceros']['_attributes']['RfcACuentaTerceros'])->toBe('AAA010101AAA');
});

test('concepto predial adds CuentaPredial', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101']);
    $concepto->predial('12345');

    $result = $concepto->getConcept();
    expect($result)->toHaveKey('cfdi:CuentaPredial');
    expect($result['cfdi:CuentaPredial']['_attributes']['Numero'])->toBe('12345');
});

test('concepto parte adds Parte with numeric coercion', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101']);
    $concepto->parte([
        'ClaveProdServ' => '02020202',
        'Cantidad' => '5',
        'ValorUnitario' => '100.50',
        'Importe' => '502.50',
        'Descripcion' => 'Parte test',
    ]);

    $result = $concepto->getConcept();
    expect($result)->toHaveKey('cfdi:Parte');
    expect($result['cfdi:Parte']['_attributes']['Cantidad'])->toBe(5.0);
    expect($result['cfdi:Parte']['_attributes']['ValorUnitario'])->toBe(100.50);
    expect($result['cfdi:Parte']['_attributes']['Importe'])->toBe(502.50);
});

test('concepto setParteInformacionAduanera after parte', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101']);
    $concepto->parte([
        'ClaveProdServ' => '02020202',
        'Cantidad' => 1,
        'ValorUnitario' => 100,
        'Importe' => 100,
    ]);
    $concepto->setParteInformacionAduanera('21 47 3807 8003832');

    $result = $concepto->getConcept();
    expect($result['cfdi:Parte'])->toHaveKey('cfdi:InformacionAduanera');
    expect($result['cfdi:Parte']['cfdi:InformacionAduanera'])->toHaveCount(1);
    expect($result['cfdi:Parte']['cfdi:InformacionAduanera'][0]['_attributes']['NumeroPedimento'])
        ->toBe('21 47 3807 8003832');
});

test('concepto setParteInformacionAduanera without parte does nothing', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101']);
    $concepto->setParteInformacionAduanera('21 47 3807 8003832');

    $result = $concepto->getConcept();
    expect($result)->not->toHaveKey('cfdi:Parte');
});

test('concepto InformacionAduanera on concepto', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101']);
    $concepto->InformacionAduanera('21 47 3807 8003832');

    $result = $concepto->getConcept();
    expect($result)->toHaveKey('cfdi:InformacionAduanera');
    expect($result['cfdi:InformacionAduanera'])->toHaveCount(1);
});

test('concepto traslado adds impuestos', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101']);
    $concepto->traslado([
        'Base' => 130,
        'Impuesto' => '002',
        'TipoFactor' => 'Tasa',
        'TasaOCuota' => '0.160000',
        'Importe' => 20.80,
    ]);

    $result = $concepto->getConcept();
    expect($result)->toHaveKey('cfdi:Impuestos');
    $traslados = $result['cfdi:Impuestos']['cfdi:Traslados']['cfdi:Traslado'];
    expect($traslados)->toHaveCount(1);
    expect($traslados[0]['_attributes']['Base'])->toBe(130);
    expect($traslados[0]['_attributes']['Impuesto'])->toBe('002');
});

test('concepto retencion adds retenciones', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101']);
    $concepto->retencion([
        'Base' => 1000,
        'Impuesto' => '001',
        'TipoFactor' => 'Tasa',
        'TasaOCuota' => '0.100000',
        'Importe' => 100,
    ]);

    $result = $concepto->getConcept();
    expect($result)->toHaveKey('cfdi:Impuestos');
    $retenciones = $result['cfdi:Impuestos']['cfdi:Retenciones']['cfdi:Retencion'];
    expect($retenciones)->toHaveCount(1);
    expect($retenciones[0]['_attributes']['Impuesto'])->toBe('001');
});

test('concepto isComplement returns false by default', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101']);
    expect($concepto->isComplement())->toBe(false);
});

test('concepto getConcept resets internal state', function () {
    $concepto = new Concepto(['ClaveProdServ' => '01010101', 'Cantidad' => 1]);
    $first = $concepto->getConcept();
    $second = $concepto->getConcept();

    expect($first['_attributes']['ClaveProdServ'])->toBe('01010101');
    expect($second)->toBe([]);
});
