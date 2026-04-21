<?php

use Cfdi\Transform\XsltParser;

$filesDir = dirname(__DIR__, 4) . '/../cfdi-node/packages/files';
$xsltPath = "{$filesDir}/4.0/cadenaoriginal.xslt";

describe('xslt parser', function () use ($xsltPath) {
    test('should parse cadenaoriginal.xslt and extract templates', function () use ($xsltPath) {
        $registry = XsltParser::parse($xsltPath);

        expect(count($registry->templates))->toBeGreaterThan(0);
        expect(isset($registry->templates['cfdi:Comprobante']))->toBeTrue();
        expect(isset($registry->templates['cfdi:Emisor']))->toBeTrue();
        expect(isset($registry->templates['cfdi:Receptor']))->toBeTrue();
        expect(isset($registry->templates['cfdi:Concepto']))->toBeTrue();
        expect(isset($registry->templates['cfdi:Impuestos']))->toBeTrue();
        expect(isset($registry->templates['cfdi:Complemento']))->toBeTrue();
    });

    test('should parse complemento templates', function () use ($xsltPath) {
        $registry = XsltParser::parse($xsltPath);

        expect(isset($registry->templates['vehiculousado:VehiculoUsado']))->toBeTrue();
        expect(isset($registry->templates['pago20:Pagos']))->toBeTrue();
        expect(isset($registry->templates['nomina12:Nomina']))->toBeTrue();
    });

    test('should extract correct attribute order for Comprobante', function () use ($xsltPath) {
        $registry = XsltParser::parse($xsltPath);
        $comprobante = $registry->templates['cfdi:Comprobante'];
        $attrRules = array_values(array_filter($comprobante->rules, fn($r) => $r->type === 'attr'));

        expect($attrRules[0]->type)->toBe('attr');
        expect($attrRules[0]->name)->toBe('Version');
        expect($attrRules[0]->required)->toBeTrue();

        expect($attrRules[1]->type)->toBe('attr');
        expect($attrRules[1]->name)->toBe('Serie');
        expect($attrRules[1]->required)->toBeFalse();

        expect($attrRules[2]->type)->toBe('attr');
        expect($attrRules[2]->name)->toBe('Folio');
        expect($attrRules[2]->required)->toBeFalse();

        expect($attrRules[3]->type)->toBe('attr');
        expect($attrRules[3]->name)->toBe('Fecha');
        expect($attrRules[3]->required)->toBeTrue();
    });

    test('should distinguish Requerido from Opcional', function () use ($xsltPath) {
        $registry = XsltParser::parse($xsltPath);
        $emisor = $registry->templates['cfdi:Emisor'];
        $attrRules = array_values(array_filter($emisor->rules, fn($r) => $r->type === 'attr'));

        expect($attrRules[0]->type)->toBe('attr');
        expect($attrRules[0]->name)->toBe('Rfc');
        expect($attrRules[0]->required)->toBeTrue();

        expect($attrRules[3]->type)->toBe('attr');
        expect($attrRules[3]->name)->toBe('FacAtrAdquirente');
        expect($attrRules[3]->required)->toBeFalse();
    });

    test('should extract namespaces from XSLT', function () use ($xsltPath) {
        $registry = XsltParser::parse($xsltPath);

        expect($registry->namespaces['cfdi'])->toBe('http://www.sat.gob.mx/cfd/4');
        expect($registry->namespaces['pago20'])->toBe('http://www.sat.gob.mx/Pagos20');
        expect($registry->namespaces['nomina12'])->toBe('http://www.sat.gob.mx/nomina12');
        expect($registry->namespaces['vehiculousado'])->toBe('http://www.sat.gob.mx/vehiculousado');
    });
});
