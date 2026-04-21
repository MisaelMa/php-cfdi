<?php

use Cfdi\Expresiones\Transform;

describe('Transform', function () {

    test('run() produce formato ||...|...|...|| desde comprobante tipo xml-js', function () {
        $xml = [
            'cfdi:Comprobante' => [
                '_attributes' => [
                    'xmlns:cfdi' => 'http://www.sat.gob.mx/cfd/4',
                    'Version' => '4.0',
                    'Fecha' => '2024-01-15T12:00:00',
                    'Sello' => 'NO_DEBE_APARECER',
                    'Certificado' => 'TAMPOCO',
                ],
                'cfdi:Emisor' => [
                    '_attributes' => [
                        'Rfc' => 'EKU9003173C9',
                        'Nombre' => 'EMISOR',
                        'RegimenFiscal' => '601',
                    ],
                ],
                'cfdi:Receptor' => [
                    '_attributes' => [
                        'Rfc' => 'URE180429TM6',
                        'UsoCFDI' => 'G01',
                    ],
                ],
            ],
        ];

        $expected = '||4.0|2024-01-15T12:00:00|EKU9003173C9|EMISOR|601|URE180429TM6|G01||';

        expect((new Transform($xml))->run())->toBe($expected);
    });

    test('omite Sello y Certificado', function () {
        $xml = [
            'cfdi:Comprobante' => [
                '_attributes' => [
                    'Version' => '4.0',
                    'Sello' => 'SELLO_SECRETO',
                    'Certificado' => 'CERT_SECRETO',
                    'Serie' => 'F',
                ],
            ],
        ];

        $out = (new Transform($xml))->run();

        expect($out)->toBe('||4.0|F||');
        expect($out)->not->toContain('SELLO_SECRETO');
        expect($out)->not->toContain('CERT_SECRETO');
    });

    test('entrada vacía o sin Comprobante devuelve ||||', function () {
        expect((new Transform([]))->run())->toBe('||||');
        expect((new Transform(['cfdi:Comprobante' => []]))->run())->toBe('||||');
    });
});
