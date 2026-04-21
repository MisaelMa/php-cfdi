<?php

use Sat\Cfdi\Receptor;

test('receptor constructor stores attributes', function () {
    $receptor = new Receptor([
        'Rfc' => 'URE180429TM6',
        'Nombre' => 'UNIVERSIDAD ROBOTICA ESPANOLA',
        'DomicilioFiscalReceptor' => '86991',
        'RegimenFiscalReceptor' => '601',
        'UsoCFDI' => 'G01',
    ]);

    $result = $receptor->toArray();
    expect($result['_attributes']['Rfc'])->toBe('URE180429TM6');
    expect($result['_attributes']['Nombre'])->toBe('UNIVERSIDAD ROBOTICA ESPANOLA');
    expect($result['_attributes']['UsoCFDI'])->toBe('G01');
});

test('receptor setRFC updates RFC', function () {
    $receptor = new Receptor(['Rfc' => '']);
    $receptor->setRFC('XAXX010101000');

    expect($receptor->toArray()['_attributes']['Rfc'])->toBe('XAXX010101000');
});

test('receptor setNombre updates Nombre', function () {
    $receptor = new Receptor(['Rfc' => '']);
    $receptor->setNombre('Test Receptor');

    expect($receptor->toArray()['_attributes']['Nombre'])->toBe('Test Receptor');
});

test('receptor setUsoCFDI updates UsoCFDI', function () {
    $receptor = new Receptor(['Rfc' => '']);
    $receptor->setUsoCFDI('P01');

    expect($receptor->toArray()['_attributes']['UsoCFDI'])->toBe('P01');
});

test('receptor setDomicilioFiscalReceptor updates domicilio', function () {
    $receptor = new Receptor(['Rfc' => '']);
    $receptor->setDomicilioFiscalReceptor('86991');

    expect($receptor->toArray()['_attributes']['DomicilioFiscalReceptor'])->toBe('86991');
});

test('receptor setResidenciaFiscal updates residencia', function () {
    $receptor = new Receptor(['Rfc' => '']);
    $receptor->setResidenciaFiscal('MEX');

    expect($receptor->toArray()['_attributes']['ResidenciaFiscal'])->toBe('MEX');
});

test('receptor setNumRegIdTrib updates NumRegIdTrib', function () {
    $receptor = new Receptor(['Rfc' => '']);
    $receptor->setNumRegIdTrib('123456789');

    expect($receptor->toArray()['_attributes']['NumRegIdTrib'])->toBe('123456789');
});

test('receptor setRegimenFiscalReceptor updates regimen', function () {
    $receptor = new Receptor(['Rfc' => '']);
    $receptor->setRegimenFiscalReceptor('601');

    expect($receptor->toArray()['_attributes']['RegimenFiscalReceptor'])->toBe('601');
});

test('receptor toArray returns _attributes structure', function () {
    $receptor = new Receptor(['Rfc' => 'URE180429TM6']);

    $result = $receptor->toArray();
    expect($result)->toHaveKey('_attributes');
    expect($result['_attributes'])->toBeArray();
});
