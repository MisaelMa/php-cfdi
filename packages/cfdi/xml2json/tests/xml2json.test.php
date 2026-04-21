<?php

use Cfdi\Xml2Json\XmlToJson;

$FILES_DIR = dirname(__DIR__, 4) . '/../cfdi-node/packages/files/xml';

describe('XmlToJson', function () use ($FILES_DIR) {

    test('Emisor & Receptor', function () use ($FILES_DIR) {
        $xml = "{$FILES_DIR}/emisor-receptor.xml";
        $json = XmlToJson::convert($xml);

        expect($json)->toBe([
            'Comprobante' => [
                'Emisor' => [
                    'Rfc' => 'EKU9003173C9',
                    'Nombre' => 'ESCUELA KEMPER URGATE',
                    'RegimenFiscal' => '603',
                ],
                'Receptor' => [
                    'Rfc' => 'CACX7605101P8',
                    'Nombre' => 'XOCHILT CASAS CHAVEZ',
                    'DomicilioFiscalReceptor' => '36257',
                    'RegimenFiscalReceptor' => '612',
                    'UsoCFDI' => 'G03',
                ],
            ],
        ]);
    });

    test('un concepto', function () use ($FILES_DIR) {
        $xml = "{$FILES_DIR}/un-concepto.xml";
        $json = XmlToJson::convert($xml);

        expect($json)->toBe([
            'Comprobante' => [
                'Conceptos' => [
                    [
                        'ClaveProdServ' => '86121500',
                        'Cantidad' => '1',
                        'ClaveUnidad' => 'E48',
                        'Unidad' => 'Pieza',
                        'Descripcion' => 'Mensualidad - diciembre',
                        'ValorUnitario' => '5000',
                        'Importe' => '5000',
                        'Descuento' => '0',
                    ],
                ],
            ],
        ]);
    });

    test('dos conceptos', function () use ($FILES_DIR) {
        $xml = "{$FILES_DIR}/dos-conceptos.xml";
        $json = XmlToJson::convert($xml);

        expect($json)->toHaveKey('Comprobante.Conceptos');
        expect(count($json['Comprobante']['Conceptos']))->toBe(2);
    });

    test('un impuesto con traslados y retenciones', function () use ($FILES_DIR) {
        $xml = "{$FILES_DIR}/un-impuesto.xml";
        $json = XmlToJson::convert($xml);

        expect($json)->toBe([
            'Comprobante' => [
                'Impuestos' => [
                    'TotalImpuestosTrasladados' => '31.72',
                    'Traslados' => [
                        [
                            'Impuesto' => '002',
                            'TipoFactor' => 'Tasa',
                            'TasaOCuota' => '0.160000',
                            'Importe' => '31.72',
                        ],
                    ],
                    'Retenciones' => [
                        [
                            'Impuesto' => '004',
                            'Importe' => '2.00',
                        ],
                    ],
                ],
            ],
        ]);
    });

    test('dos impuestos', function () use ($FILES_DIR) {
        $xml = "{$FILES_DIR}/dos-impuestos.xml";
        $json = XmlToJson::convert($xml);

        expect($json)->toHaveKey('Comprobante.Impuestos');
        $impuestos = $json['Comprobante']['Impuestos'];
        expect(count($impuestos['Traslados']))->toBe(2);
        expect(count($impuestos['Retenciones']))->toBe(2);
    });

    test('concepto con complemento', function () use ($FILES_DIR) {
        $xml = "{$FILES_DIR}/conceptos.xml";
        $json = XmlToJson::convert($xml);

        expect($json)->toHaveKey('Comprobante.Conceptos');
        $concepto = $json['Comprobante']['Conceptos'][0];
        expect($concepto['ClaveProdServ'])->toBe('86121500');
        expect($concepto)->toHaveKey('Impuestos');
        expect($concepto['Impuestos'])->toHaveKey('Traslados');
    });

    test('acepta XML string directamente', function () {
        $xml = '<?xml version="1.0"?><cfdi:Comprobante><cfdi:Emisor Rfc="AAA010101AAA" Nombre="TEST"/></cfdi:Comprobante>';
        $json = XmlToJson::convert($xml);

        expect($json)->toHaveKey('Comprobante.Emisor');
        expect($json['Comprobante']['Emisor']['Rfc'])->toBe('AAA010101AAA');
    });

    test('con original=true preserva prefijos de namespace', function () {
        $xml = '<?xml version="1.0"?><cfdi:Comprobante><cfdi:Emisor Rfc="AAA010101AAA"/></cfdi:Comprobante>';
        $json = XmlToJson::convert($xml, original: true);

        expect($json)->toHaveKey('cfdi:Comprobante');
    });
});
