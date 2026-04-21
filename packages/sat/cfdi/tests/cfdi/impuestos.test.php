<?php

use Sat\Cfdi\Impuestos;

test('impuestos constructor stores total', function () {
    $impuestos = new Impuestos(['TotalImpuestosTrasladados' => '135.20']);

    $total = $impuestos->getTotalImpuestos();
    expect($total['TotalImpuestosTrasladados'])->toBe('135.20');
});

test('impuestos constructor handles empty', function () {
    $impuestos = new Impuestos();

    $total = $impuestos->getTotalImpuestos();
    expect($total)->toBe([]);
});

test('impuestos traslados adds traslado', function () {
    $impuestos = new Impuestos(['TotalImpuestosTrasladados' => '135.20']);
    $impuestos->traslados([
        'Base' => '844.98',
        'Impuesto' => '002',
        'TipoFactor' => 'Tasa',
        'TasaOCuota' => '0.160000',
        'Importe' => '135.20',
    ]);

    $traslados = $impuestos->getTraslados();
    expect($traslados)->toHaveCount(1);
    expect($traslados[0]['_attributes']['Base'])->toBe('844.98');
    expect($traslados[0]['_attributes']['Impuesto'])->toBe('002');
    expect($traslados[0]['_attributes']['Importe'])->toBe('135.20');
});

test('impuestos traslados sorts attributes', function () {
    $impuestos = new Impuestos();
    $impuestos->traslados([
        'Importe' => '100',
        'Base' => '625',
        'TasaOCuota' => '0.160000',
        'Impuesto' => '002',
        'TipoFactor' => 'Tasa',
    ]);

    $traslados = $impuestos->getTraslados();
    $keys = array_keys($traslados[0]['_attributes']);
    expect($keys[0])->toBe('Base');
    expect($keys[1])->toBe('Impuesto');
    expect($keys[2])->toBe('TipoFactor');
});

test('impuestos retenciones adds retencion', function () {
    $impuestos = new Impuestos(['TotalImpuestosRetenidos' => '100.00']);
    $impuestos->retenciones([
        'Impuesto' => '001',
        'Importe' => '100.00',
    ]);

    $retenciones = $impuestos->getRetenciones();
    expect($retenciones)->toHaveCount(1);
    expect($retenciones[0]['_attributes']['Impuesto'])->toBe('001');
    expect($retenciones[0]['_attributes']['Importe'])->toBe('100.00');
});

test('impuestos multiple traslados', function () {
    $impuestos = new Impuestos(['TotalImpuestosTrasladados' => '200.00']);
    $impuestos->traslados([
        'Base' => '500',
        'Impuesto' => '002',
        'TipoFactor' => 'Tasa',
        'TasaOCuota' => '0.160000',
        'Importe' => '80.00',
    ]);
    $impuestos->traslados([
        'Base' => '750',
        'Impuesto' => '002',
        'TipoFactor' => 'Tasa',
        'TasaOCuota' => '0.160000',
        'Importe' => '120.00',
    ]);

    $traslados = $impuestos->getTraslados();
    expect($traslados)->toHaveCount(2);
});

test('impuestos impuesto structure is correct', function () {
    $impuestos = new Impuestos(['TotalImpuestosTrasladados' => '135.20']);
    $impuestos->traslados([
        'Base' => '844.98',
        'Impuesto' => '002',
        'TipoFactor' => 'Tasa',
        'TasaOCuota' => '0.160000',
        'Importe' => '135.20',
    ]);

    expect($impuestos->impuesto)->toHaveKey('_attributes');
    expect($impuestos->impuesto)->toHaveKey('cfdi:Traslados');
    expect($impuestos->impuesto['cfdi:Traslados'])->toHaveKey('cfdi:Traslado');
});
