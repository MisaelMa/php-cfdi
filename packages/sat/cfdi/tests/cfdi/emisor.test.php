<?php

use Sat\Cfdi\Emisor;

test('emisor constructor stores attributes', function () {
    $emisor = new Emisor([
        'Rfc' => 'EKU9003173C9',
        'Nombre' => 'ESCUELA KEMPER URGATE',
        'RegimenFiscal' => '601',
    ]);

    $result = $emisor->toArray();
    expect($result['_attributes']['Rfc'])->toBe('EKU9003173C9');
    expect($result['_attributes']['Nombre'])->toBe('ESCUELA KEMPER URGATE');
    expect($result['_attributes']['RegimenFiscal'])->toBe('601');
});

test('emisor setRfc updates RFC', function () {
    $emisor = new Emisor(['Rfc' => '', 'Nombre' => '', 'RegimenFiscal' => '']);
    $emisor->setRfc('AAA010101AAA');

    expect($emisor->toArray()['_attributes']['Rfc'])->toBe('AAA010101AAA');
});

test('emisor setNombre updates Nombre', function () {
    $emisor = new Emisor(['Rfc' => '', 'Nombre' => '', 'RegimenFiscal' => '']);
    $emisor->setNombre('Test Company');

    expect($emisor->toArray()['_attributes']['Nombre'])->toBe('Test Company');
});

test('emisor setRegimenFiscal updates RegimenFiscal', function () {
    $emisor = new Emisor(['Rfc' => '', 'Nombre' => '', 'RegimenFiscal' => '']);
    $emisor->setRegimenFiscal('603');

    expect($emisor->toArray()['_attributes']['RegimenFiscal'])->toBe('603');
});

test('emisor setFacAtrAdquirente adds attribute', function () {
    $emisor = new Emisor(['Rfc' => 'EKU9003173C9', 'Nombre' => 'Test', 'RegimenFiscal' => '601']);
    $emisor->setFacAtrAdquirente('0001');

    expect($emisor->toArray()['_attributes']['FacAtrAdquirente'])->toBe('0001');
});

test('emisor toArray returns _attributes structure', function () {
    $emisor = new Emisor([
        'Rfc' => 'EKU9003173C9',
        'Nombre' => 'ESCUELA KEMPER URGATE',
        'RegimenFiscal' => '601',
    ]);

    $result = $emisor->toArray();
    expect($result)->toHaveKey('_attributes');
    expect($result['_attributes'])->toBeArray();
});
