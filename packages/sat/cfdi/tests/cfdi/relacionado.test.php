<?php

use Sat\Cfdi\Relacionado;

test('relacionado constructor stores attributes', function () {
    $rel = new Relacionado(['TipoRelacion' => '01']);
    $result = $rel->getRelation();

    expect($result['_attributes']['TipoRelacion'])->toBe('01');
});

test('relacionado addRelation adds UUID', function () {
    $rel = new Relacionado(['TipoRelacion' => '01']);
    $rel->addRelation('6F50B653-F5BE-4443-9C0A-2AB4F151A912');

    $result = $rel->getRelation();
    expect($result)->toHaveKey('cfdi:CfdiRelacionado');
    expect($result['cfdi:CfdiRelacionado'])->toHaveCount(1);
    expect($result['cfdi:CfdiRelacionado'][0]['_attributes']['UUID'])
        ->toBe('6F50B653-F5BE-4443-9C0A-2AB4F151A912');
});

test('relacionado addRelation adds multiple UUIDs', function () {
    $rel = new Relacionado(['TipoRelacion' => '04']);
    $rel->addRelation('6F50B653-F5BE-4443-9C0A-2AB4F151A912');
    $rel->addRelation('A1B2C3D4-E5F6-7890-ABCD-EF1234567890');

    $result = $rel->getRelation();
    expect($result['cfdi:CfdiRelacionado'])->toHaveCount(2);
    expect($result['cfdi:CfdiRelacionado'][1]['_attributes']['UUID'])
        ->toBe('A1B2C3D4-E5F6-7890-ABCD-EF1234567890');
});

test('relacionado toJson equals getRelation', function () {
    $rel = new Relacionado(['TipoRelacion' => '01']);
    $rel->addRelation('6F50B653-F5BE-4443-9C0A-2AB4F151A912');

    expect($rel->toJson())->toBe($rel->getRelation());
});
