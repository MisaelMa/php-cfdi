<?php

use Sat\Cfdi\Comprobante;
use Sat\Cfdi\Emisor;
use Sat\Cfdi\Receptor;
use Sat\Cfdi\Concepto;
use Sat\Cfdi\Impuestos;
use Sat\Cfdi\Relacionado;

test('comprobante default has Version 4.0', function () {
    $comprobante = new Comprobante();
    $xml = $comprobante->toXml();

    expect($xml['_attributes']['Version'])->toBe('4.0');
});

test('comprobante addSchemaLocation deduplicates', function () {
    $comprobante = new Comprobante();
    $comprobante->addSchemaLocation([
        'http://www.sat.gob.mx/cfd/4',
        'http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd',
    ]);
    $comprobante->addSchemaLocation([
        'http://www.sat.gob.mx/cfd/4',
        'http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd',
        'http://www.sat.gob.mx/iedu',
    ]);

    $xml = $comprobante->toXml();
    $locations = $xml['_attributes']['xsi:schemaLocation'];
    expect($locations)->toContain('http://www.sat.gob.mx/cfd/4');
    expect($locations)->toContain('http://www.sat.gob.mx/iedu');

    $parts = explode(' ', $locations);
    expect(count(array_unique($parts)))->toBe(count($parts));
});

test('comprobante addXmlns adds namespace', function () {
    $comprobante = new Comprobante();
    $comprobante->addXmlns('xmlns:cfdi', 'http://www.sat.gob.mx/cfd/4');

    $xml = $comprobante->toXml();
    expect($xml['_attributes']['xmlns:cfdi'])->toBe('http://www.sat.gob.mx/cfd/4');
});

test('comprobante comprobante() sets attributes with order', function () {
    $comprobante = new Comprobante();
    $comprobante->comprobante([
        'Fecha' => '2023-01-01T00:00:00',
        'FormaPago' => '01',
        'SubTotal' => '1000.00',
        'Moneda' => 'MXN',
        'Total' => '1160.00',
        'TipoDeComprobante' => 'I',
        'Exportacion' => '01',
        'MetodoPago' => 'PUE',
        'LugarExpedicion' => '86991',
    ]);

    $xml = $comprobante->toXml();
    $attrs = $xml['_attributes'];

    expect($attrs['Version'])->toBe('4.0');
    expect($attrs['Sello'])->toBe('');
    expect($attrs['NoCertificado'])->toBe('');
    expect($attrs['Certificado'])->toBe('');
    expect($attrs['Fecha'])->toBe('2023-01-01T00:00:00');
    expect($attrs['LugarExpedicion'])->toBe('86991');

    $keys = array_keys($attrs);
    $versionIdx = array_search('Version', $keys);
    $fechaIdx = array_search('Fecha', $keys);
    expect($versionIdx)->toBeLessThan($fechaIdx);
});

test('comprobante informacionGlobal adds node', function () {
    $comprobante = new Comprobante();
    $comprobante->informacionGlobal([
        'Periodicidad' => '01',
        'Meses' => '01',
        'Año' => '2023',
    ]);

    $xml = $comprobante->toXml();
    expect($xml)->toHaveKey('cfdi:InformacionGlobal');
    expect($xml['cfdi:InformacionGlobal']['_attributes']['Periodicidad'])->toBe('01');
});

test('comprobante emisor adds Emisor node', function () {
    $comprobante = new Comprobante();
    $emisor = new Emisor([
        'Rfc' => 'EKU9003173C9',
        'Nombre' => 'ESCUELA KEMPER URGATE',
        'RegimenFiscal' => '601',
    ]);
    $comprobante->emisor($emisor);

    $xml = $comprobante->toXml();
    expect($xml)->toHaveKey('cfdi:Emisor');
    expect($xml['cfdi:Emisor']['_attributes']['Rfc'])->toBe('EKU9003173C9');
});

test('comprobante receptor adds Receptor node', function () {
    $comprobante = new Comprobante();
    $receptor = new Receptor([
        'Rfc' => 'URE180429TM6',
        'Nombre' => 'UNIVERSIDAD ROBOTICA ESPANOLA',
        'UsoCFDI' => 'G01',
    ]);
    $comprobante->receptor($receptor);

    $xml = $comprobante->toXml();
    expect($xml)->toHaveKey('cfdi:Receptor');
    expect($xml['cfdi:Receptor']['_attributes']['Rfc'])->toBe('URE180429TM6');
});

test('comprobante concepto adds to Conceptos', function () {
    $comprobante = new Comprobante();
    $concepto = new Concepto([
        'ClaveProdServ' => '01010101',
        'Cantidad' => 1,
        'ClaveUnidad' => 'ACT',
        'Descripcion' => 'Venta',
        'ValorUnitario' => 100,
        'Importe' => 100,
    ]);
    $comprobante->concepto($concepto);

    $xml = $comprobante->toXml();
    expect($xml)->toHaveKey('cfdi:Conceptos');
    expect($xml['cfdi:Conceptos']['cfdi:Concepto'])->toHaveCount(1);
});

test('comprobante multiple conceptos', function () {
    $comprobante = new Comprobante();

    $concepto1 = new Concepto(['ClaveProdServ' => '01010101', 'Cantidad' => 1, 'ClaveUnidad' => 'ACT', 'Descripcion' => 'Item 1', 'ValorUnitario' => 100, 'Importe' => 100]);
    $concepto2 = new Concepto(['ClaveProdServ' => '01010101', 'Cantidad' => 2, 'ClaveUnidad' => 'ACT', 'Descripcion' => 'Item 2', 'ValorUnitario' => 200, 'Importe' => 400]);

    $comprobante->concepto($concepto1);
    $comprobante->concepto($concepto2);

    $xml = $comprobante->toXml();
    expect($xml['cfdi:Conceptos']['cfdi:Concepto'])->toHaveCount(2);
});

test('comprobante impuesto adds Impuestos', function () {
    $comprobante = new Comprobante();
    $impuestos = new Impuestos(['TotalImpuestosTrasladados' => '135.20']);
    $impuestos->traslados([
        'Base' => '844.98',
        'Impuesto' => '002',
        'TipoFactor' => 'Tasa',
        'TasaOCuota' => '0.160000',
        'Importe' => '135.20',
    ]);
    $comprobante->impuesto($impuestos);

    $xml = $comprobante->toXml();
    expect($xml)->toHaveKey('cfdi:Impuestos');
    expect($xml['cfdi:Impuestos']['_attributes']['TotalImpuestosTrasladados'])->toBe('135.20');
});

test('comprobante setCertificado sets Certificado attribute', function () {
    $comprobante = new Comprobante();
    $comprobante->comprobante([]);
    $comprobante->setCertificado('MIICERTIFICADOBASE64');

    $xml = $comprobante->toXml();
    expect($xml['_attributes']['Certificado'])->toBe('MIICERTIFICADOBASE64');
});

test('comprobante setNoCertificado sets NoCertificado attribute', function () {
    $comprobante = new Comprobante();
    $comprobante->comprobante([]);
    $comprobante->setNoCertificado('30001000000300023708');

    $xml = $comprobante->toXml();
    expect($xml['_attributes']['NoCertificado'])->toBe('30001000000300023708');
});

test('comprobante setSello sets Sello attribute', function () {
    $comprobante = new Comprobante();
    $comprobante->comprobante([]);
    $comprobante->setSello('SELLOBASE64TEST');

    $xml = $comprobante->toXml();
    expect($xml['_attributes']['Sello'])->toBe('SELLOBASE64TEST');
});

test('comprobante restartCfdi resets xml', function () {
    $comprobante = new Comprobante();
    $emisor = new Emisor(['Rfc' => 'EKU9003173C9', 'Nombre' => 'Test', 'RegimenFiscal' => '601']);
    $comprobante->emisor($emisor);

    // After restart, emisor should still have default structure
    $reflection = new \ReflectionMethod($comprobante, 'restartCfdi');
    $reflection->setAccessible(true);
    $reflection->invoke($comprobante);

    $xml = $comprobante->toXml();
    expect($xml['cfdi:Emisor'])->toBe([]);
    expect($xml['cfdi:Receptor'])->toBe([]);
});

test('comprobante relacionados adds CfdiRelacionados', function () {
    $comprobante = new Comprobante();
    $rel = new Relacionado(['TipoRelacion' => '01']);
    $rel->addRelation('6F50B653-F5BE-4443-9C0A-2AB4F151A912');
    $comprobante->relacionados($rel);

    $xml = $comprobante->toXml();
    expect($xml)->toHaveKey('cfdi:CfdiRelacionados');
});
