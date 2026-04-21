<?php

use Cfdi\Retenciones\EmisorRetencion;
use Cfdi\Retenciones\NacionalidadReceptor;
use Cfdi\Retenciones\PeriodoRetencion;
use Cfdi\Retenciones\ReceptorNacional;
use Cfdi\Retenciones\ReceptorRetencion;
use Cfdi\Retenciones\Retencion20;
use Cfdi\Retenciones\Retencion20Builder;
use Cfdi\Retenciones\TipoRetencion;
use Cfdi\Retenciones\TotalesRetencion;

describe('Retencion20Builder', function () {

    test('build() genera XML Retenciones 2.0 con receptor nacional', function () {
        $data = [
            'CveRetenc' => TipoRetencion::Dividendos->value,
            'FechaExp' => '2024-01-15T12:00:00',
            'LugarExpRet' => '12345',
            'emisor' => [
                'Rfc' => 'EKU9003173C9',
                'NomDenRazSocE' => 'EMISOR SA',
                'RegimenFiscalE' => '601',
                'CurpE' => 'CURP123456HDFABC01',
            ],
            'receptor' => [
                'NacionalidadR' => NacionalidadReceptor::Nacional->value,
                'nacional' => [
                    'RfcRecep' => 'URE180429TM6',
                    'NomDenRazSocR' => 'RECEPTOR',
                ],
            ],
            'periodo' => [
                'MesIni' => '01',
                'MesFin' => '01',
                'Ejerc' => '2024',
            ],
            'totales' => [
                'montoTotOperacion' => '1000.00',
                'montoTotGrav' => '1000.00',
                'montoTotExent' => '0.00',
                'montoTotRet' => '100.00',
            ],
        ];

        $xml = Retencion20Builder::build($data);

        expect($xml)->toStartWith('<?xml version="1.0" encoding="UTF-8"?>');
        expect($xml)->toContain('xmlns:retenciones="' . Retencion20Builder::RETENCION_PAGO_NAMESPACE_V2 . '"');
        expect($xml)->toContain('CveRetenc="16"');
        expect($xml)->toContain('<retenciones:Emisor Rfc="EKU9003173C9" NomDenRazSocE="EMISOR SA" RegimenFiscalE="601" CURPE="CURP123456HDFABC01"/>');
        expect($xml)->toContain('<retenciones:Receptor Nacionalidad="Nacional">');
        expect($xml)->toContain('<retenciones:Nacional RFCRecep="URE180429TM6" NomDenRazSocR="RECEPTOR"/>');
        expect($xml)->toContain('<retenciones:Periodo MesIni="01" MesFin="01" Ejerc="2024"/>');
        expect($xml)->toContain('montoTotOperacion="1000.00"');
    });

    test('build() receptor extranjero y complemento', function () {
        $data = [
            'CveRetenc' => '99',
            'DescRetenc' => 'Otro',
            'FechaExp' => '2024-06-01T00:00:00',
            'LugarExpRet' => '99999',
            'NumCert' => 'CERT001',
            'FolioInt' => 'F-1',
            'emisor' => [
                'Rfc' => 'AAA010101AAA',
                'RegimenFiscalE' => '601',
            ],
            'receptor' => [
                'NacionalidadR' => 'Extranjero',
                'extranjero' => [
                    'NumRegIdTrib' => 'FOREIGN-1',
                    'NomDenRazSocR' => 'Foreign Co',
                ],
            ],
            'periodo' => ['MesIni' => '06', 'MesFin' => '06', 'Ejerc' => '2024'],
            'totales' => [
                'montoTotOperacion' => '1.00',
                'montoTotGrav' => '1.00',
                'montoTotExent' => '0.00',
                'montoTotRet' => '0.10',
            ],
            'complemento' => [
                ['innerXml' => '<custom:Foo xmlns:custom="urn:test"/>', 'meta' => ['k' => 1]],
            ],
        ];

        $xml = Retencion20Builder::build($data);

        expect($xml)->toContain('DescRetenc="Otro"');
        expect($xml)->toContain('NumCert="CERT001"');
        expect($xml)->toContain('FolioInt="F-1"');
        expect($xml)->toContain('<retenciones:Extranjero NumRegIdTrib="FOREIGN-1" NomDenRazSocR="Foreign Co"/>');
        expect($xml)->toContain('<retenciones:Complemento><custom:Foo xmlns:custom="urn:test"/></retenciones:Complemento>');
    });

    test('escapa caracteres especiales en atributos', function () {
        $data = [
            'CveRetenc' => '14',
            'FechaExp' => '2024-01-01T00:00:00',
            'LugarExpRet' => '12345',
            'emisor' => [
                'Rfc' => 'EKU9003173C9',
                'NomDenRazSocE' => 'A & B < "C"',
                'RegimenFiscalE' => '601',
            ],
            'receptor' => [
                'NacionalidadR' => 'Nacional',
                'nacional' => ['RfcRecep' => 'URE180429TM6'],
            ],
            'periodo' => ['MesIni' => '01', 'MesFin' => '01', 'Ejerc' => '2024'],
            'totales' => [
                'montoTotOperacion' => '0.00',
                'montoTotGrav' => '0.00',
                'montoTotExent' => '0.00',
                'montoTotRet' => '0.00',
            ],
        ];

        $xml = Retencion20Builder::build($data);

        expect($xml)->toContain('NomDenRazSocE="A &amp; B &lt; &quot;C&quot;"');
    });
});

describe('Retencion20', function () {

    test('constructor con argumentos nombrados', function () {
        $doc = new Retencion20(
            CveRetenc: TipoRetencion::Arrendamiento->value,
            FechaExp: '2024-01-01T00:00:00',
            LugarExpRet: '12345',
            emisor: new EmisorRetencion(
                Rfc: 'EKU9003173C9',
                RegimenFiscalE: '601',
            ),
            receptor: new ReceptorRetencion(
                NacionalidadR: NacionalidadReceptor::Nacional,
                nacional: new ReceptorNacional(RfcRecep: 'URE180429TM6'),
            ),
            periodo: new PeriodoRetencion(MesIni: '01', MesFin: '01', Ejerc: '2024'),
            totales: new TotalesRetencion(
                montoTotOperacion: '0.00',
                montoTotGrav: '0.00',
                montoTotExent: '0.00',
                montoTotRet: '0.00',
            ),
        );

        expect($doc->Version)->toBe('2.0');
        expect($doc->CveRetenc)->toBe('14');
    });
});
