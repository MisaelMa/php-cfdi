<?php

use Cfdi\Xsd\Schema;
use Cfdi\Xsd\SchemaKey;

afterEach(function () {
    Schema::reset();
});

test('Schema carga cfdi.json y expone getSchema', function () {
    $dir = sys_get_temp_dir() . '/cfdi-xsd-facade-' . bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);
    mkdir($dir . '/emisor', 0777, true);
    file_put_contents($dir . '/cfdi.json', json_encode([
        'catalogos' => [],
        'comprobante' => [
            [
                'name' => 'emisor',
                'type' => 'comprobante',
                'key' => SchemaKey::COMPROBANTE_EMISOR,
                'path' => 'emisor',
            ],
        ],
        'complementos' => [],
    ], JSON_THROW_ON_ERROR));
    file_put_contents($dir . '/emisor/emisor.json', json_encode([
        'type' => 'object',
        'required' => ['Rfc', 'Nombre'],
    ], JSON_THROW_ON_ERROR));

    $schema = Schema::of();
    $schema->setConfig(['path' => $dir]);

    $loaded = $schema->getSchema(SchemaKey::COMPROBANTE_EMISOR);
    expect($loaded)->not->toBeNull();
    expect($loaded['required'])->toContain('Rfc');

    $v = $schema->validatorFor(SchemaKey::COMPROBANTE_EMISOR);
    expect($v)->not->toBeNull();
    expect($v(['Rfc' => 'X', 'Nombre' => 'Y']))->toBeTrue();
    expect($v(['Rfc' => 'X']))->toBeFalse();
});
