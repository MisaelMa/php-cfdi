<?php

use Cfdi\Validador\Validador;

$cfdi40Dir = dirname(__DIR__, 4) . '/../cfdi-node/packages/files/xml/examples/test-cfdi40';
$cfdi33Dir = dirname(__DIR__, 4) . '/../cfdi-node/packages/files/xml/examples/test-cfdi33';

describe('Validador - XMLs validos CFDI 4.0', function () use ($cfdi40Dir) {
    $validador = new Validador();

    test('valida ingreso-basico.xml sin errores', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/ingreso-basico.xml");
        expect($result->version)->toBe('4.0');
        expect($result->errors)->toHaveCount(0);
        expect($result->valid)->toBeTrue();
    });

    test('valida ingreso-dolares.xml sin errores', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/ingreso-dolares.xml");
        expect($result->version)->toBe('4.0');
        expect($result->errors)->toHaveCount(0);
        expect($result->valid)->toBeTrue();
    });

    test('valida ingreso-exento.xml sin errores', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/ingreso-exento.xml");
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });

    test('valida ingreso-iva-retencion.xml sin errores', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/ingreso-iva-retencion.xml");
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });

    test('valida ingreso-ieps.xml sin errores', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/ingreso-ieps.xml");
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });

    test('valida egreso-nota-credito.xml sin errores', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/egreso-nota-credito.xml");
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });

    test('valida traslado.xml sin errores', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/traslado.xml");
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });

    test('valida ingreso-sin-impuestos.xml sin errores', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/ingreso-sin-impuestos.xml");
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });

    test('valida ingreso-multi-concepto.xml sin errores', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/ingreso-multi-concepto.xml");
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });
});

describe('Validador - XMLs validos CFDI 3.3', function () use ($cfdi33Dir) {
    $validador = new Validador();

    test('valida ingreso-basico.xml CFDI 3.3 sin errores', function () use ($validador, $cfdi33Dir) {
        $result = $validador->validateFile("{$cfdi33Dir}/ingreso-basico.xml");
        expect($result->version)->toBe('3.3');
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });

    test('valida ingreso-iva-retencion.xml CFDI 3.3 sin errores', function () use ($validador, $cfdi33Dir) {
        $result = $validador->validateFile("{$cfdi33Dir}/ingreso-iva-retencion.xml");
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });

    test('valida traslado.xml CFDI 3.3 sin errores', function () use ($validador, $cfdi33Dir) {
        $result = $validador->validateFile("{$cfdi33Dir}/traslado.xml");
        expect($result->valid)->toBeTrue();
        expect($result->errors)->toHaveCount(0);
    });
});

describe('Validador - version invalida', function () {
    $validador = new Validador();

    test('reporta error cuando la version es invalida', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="2.0" TipoDeComprobante="I" Fecha="2024-01-01T00:00:00"
  LugarExpedicion="06600" SubTotal="0" Total="0" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" Cantidad="1" ClaveUnidad="E48" Descripcion="Test" ValorUnitario="0" Importe="0" ObjetoImp="01"/>
  </cfdi:Conceptos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect($result->valid)->toBeFalse();
        $hasCfdi001 = false;
        foreach ($result->errors as $e) {
            if ($e->code === 'CFDI001') { $hasCfdi001 = true; break; }
        }
        expect($hasCfdi001)->toBeTrue();
    });
});

describe('Validador - estructura de resultado', function () use ($cfdi40Dir) {
    $validador = new Validador();

    test('resultado tiene la estructura correcta', function () use ($validador, $cfdi40Dir) {
        $result = $validador->validateFile("{$cfdi40Dir}/ingreso-basico.xml");
        expect($result)->toHaveProperty('valid');
        expect($result)->toHaveProperty('errors');
        expect($result)->toHaveProperty('warnings');
        expect($result)->toHaveProperty('version');
        expect(is_array($result->errors))->toBeTrue();
        expect(is_array($result->warnings))->toBeTrue();
    });

    test('cada issue tiene code, message y rule', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I" Fecha="2024-01-01T00:00:00"
  LugarExpedicion="06600" SubTotal="1000.00" Total="9999.00" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" Cantidad="1" ClaveUnidad="E48" Descripcion="Test" ValorUnitario="1000.00" Importe="1000.00" ObjetoImp="01"/>
  </cfdi:Conceptos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        foreach ($result->errors as $issue) {
            expect($issue)->toHaveProperty('code');
            expect($issue)->toHaveProperty('message');
            expect($issue)->toHaveProperty('rule');
        }
    });
});
