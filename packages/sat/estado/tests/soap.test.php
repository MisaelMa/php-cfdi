<?php

use Cfdi\Estado\Soap;
use Cfdi\Estado\ConsultaParams;

$PARAMS = new ConsultaParams(
    rfcEmisor: 'AAA010101AAA',
    rfcReceptor: 'BBB020202BBB',
    total: '1000.00',
    uuid: 'CEE4BE01-ADFA-4DEB-8421-ADD60F0BEDAC',
);

$RESPONSE_VIGENTE = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <ConsultaResponse xmlns="http://tempuri.org/">
      <ConsultaResult>
        <a:CodigoEstatus>S - Comprobante obtenido satisfactoriamente.</a:CodigoEstatus>
        <a:EsCancelable>Cancelable con aceptación</a:EsCancelable>
        <a:Estado>Vigente</a:Estado>
        <a:EstatusCancelacion></a:EstatusCancelacion>
        <a:ValidacionEFOS>200</a:ValidacionEFOS>
      </ConsultaResult>
    </ConsultaResponse>
  </s:Body>
</s:Envelope>';

$RESPONSE_CANCELADO = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <ConsultaResponse xmlns="http://tempuri.org/">
      <ConsultaResult>
        <a:CodigoEstatus>S - Comprobante obtenido satisfactoriamente.</a:CodigoEstatus>
        <a:EsCancelable>No cancelable</a:EsCancelable>
        <a:Estado>Cancelado</a:Estado>
        <a:EstatusCancelacion>Cancelado sin aceptación</a:EstatusCancelacion>
        <a:ValidacionEFOS>200</a:ValidacionEFOS>
      </ConsultaResult>
    </ConsultaResponse>
  </s:Body>
</s:Envelope>';

$RESPONSE_NO_ENCONTRADO = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <ConsultaResponse xmlns="http://tempuri.org/">
      <ConsultaResult>
        <a:CodigoEstatus>N - 601: La expresión impresa proporcionada no es válida.</a:CodigoEstatus>
        <a:EsCancelable></a:EsCancelable>
        <a:Estado>No Encontrado</a:Estado>
        <a:EstatusCancelacion></a:EstatusCancelacion>
        <a:ValidacionEFOS></a:ValidacionEFOS>
      </ConsultaResult>
    </ConsultaResponse>
  </s:Body>
</s:Envelope>';

$RESPONSE_SOAP_FAULT = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <s:Fault>
      <faultcode>s:Client</faultcode>
      <faultstring xml:lang="es-MX">Error en la peticion</faultstring>
    </s:Fault>
  </s:Body>
</s:Envelope>';

// ---------------------------------------------------------------------------
// formatTotal
// ---------------------------------------------------------------------------
describe('formatTotal', function () {

    test('formatea un entero sin decimales', function () {
        expect(Soap::formatTotal('1000'))->toBe('0000001000.000000');
    });

    test('formatea con dos decimales', function () {
        expect(Soap::formatTotal('1000.00'))->toBe('0000001000.000000');
    });

    test('formatea con decimales parciales', function () {
        expect(Soap::formatTotal('250.5'))->toBe('0000000250.500000');
    });

    test('formatea cero', function () {
        expect(Soap::formatTotal('0'))->toBe('0000000000.000000');
    });

    test('formatea un monto grande', function () {
        expect(Soap::formatTotal('9999999999.999999'))->toBe('9999999999.999999');
    });

    test('lanza error con un valor no numerico', function () {
        Soap::formatTotal('abc');
    })->throws(\InvalidArgumentException::class, 'Total invalido');
});

// ---------------------------------------------------------------------------
// buildSoapRequest
// ---------------------------------------------------------------------------
describe('buildSoapRequest', function () use ($PARAMS) {

    test('genera un envelope SOAP valido', function () use ($PARAMS) {
        $xml = Soap::buildSoapRequest($PARAMS);
        expect($xml)->toContain('soap:Envelope');
        expect($xml)->toContain('soap:Body');
        expect($xml)->toContain('tem:Consulta');
        expect($xml)->toContain('tem:expresionImpresa');
    });

    test('incluye el namespace de tempuri', function () use ($PARAMS) {
        $xml = Soap::buildSoapRequest($PARAMS);
        expect($xml)->toContain('xmlns:tem="http://tempuri.org/"');
    });

    test('incluye todos los parametros en la expresion impresa', function () use ($PARAMS) {
        $xml = Soap::buildSoapRequest($PARAMS);
        expect($xml)->toContain('re=AAA010101AAA');
        expect($xml)->toContain('rr=BBB020202BBB');
        expect($xml)->toContain('tt=0000001000.000000');
        expect($xml)->toContain('id=CEE4BE01-ADFA-4DEB-8421-ADD60F0BEDAC');
    });

    test('envuelve la expresion en CDATA', function () use ($PARAMS) {
        $xml = Soap::buildSoapRequest($PARAMS);
        expect($xml)->toContain('<![CDATA[');
        expect($xml)->toContain(']]>');
    });

    test('la expresion comienza con ?re=', function () use ($PARAMS) {
        $xml = Soap::buildSoapRequest($PARAMS);
        expect($xml)->toContain('<![CDATA[?re=');
    });
});

// ---------------------------------------------------------------------------
// parseSoapResponse - CFDI Vigente
// ---------------------------------------------------------------------------
describe('parseSoapResponse', function () use ($RESPONSE_VIGENTE, $RESPONSE_CANCELADO, $RESPONSE_NO_ENCONTRADO, $RESPONSE_SOAP_FAULT) {

    describe('CFDI Vigente', function () use ($RESPONSE_VIGENTE) {

        test('extrae el codigo de estatus', function () use ($RESPONSE_VIGENTE) {
            $result = Soap::parseSoapResponse($RESPONSE_VIGENTE);
            expect($result->codigoEstatus)->toBe('S - Comprobante obtenido satisfactoriamente.');
        });

        test('extrae esCancelable', function () use ($RESPONSE_VIGENTE) {
            $result = Soap::parseSoapResponse($RESPONSE_VIGENTE);
            expect($result->esCancelable)->toBe('Cancelable con aceptación');
        });

        test('extrae el estado como Vigente', function () use ($RESPONSE_VIGENTE) {
            $result = Soap::parseSoapResponse($RESPONSE_VIGENTE);
            expect($result->estado)->toBe('Vigente');
        });

        test('estatusCancelacion vacio', function () use ($RESPONSE_VIGENTE) {
            $result = Soap::parseSoapResponse($RESPONSE_VIGENTE);
            expect($result->estatusCancelacion)->toBe('');
        });

        test('extrae validacionEFOS', function () use ($RESPONSE_VIGENTE) {
            $result = Soap::parseSoapResponse($RESPONSE_VIGENTE);
            expect($result->validacionEFOS)->toBe('200');
        });

        test('helper activo es true', function () use ($RESPONSE_VIGENTE) {
            $result = Soap::parseSoapResponse($RESPONSE_VIGENTE);
            expect($result->activo)->toBeTrue();
        });

        test('helper cancelado es false', function () use ($RESPONSE_VIGENTE) {
            $result = Soap::parseSoapResponse($RESPONSE_VIGENTE);
            expect($result->cancelado)->toBeFalse();
        });

        test('helper noEncontrado es false', function () use ($RESPONSE_VIGENTE) {
            $result = Soap::parseSoapResponse($RESPONSE_VIGENTE);
            expect($result->noEncontrado)->toBeFalse();
        });
    });

    describe('CFDI Cancelado', function () use ($RESPONSE_CANCELADO) {

        test('extrae el estado como Cancelado', function () use ($RESPONSE_CANCELADO) {
            $result = Soap::parseSoapResponse($RESPONSE_CANCELADO);
            expect($result->estado)->toBe('Cancelado');
        });

        test('extrae estatusCancelacion', function () use ($RESPONSE_CANCELADO) {
            $result = Soap::parseSoapResponse($RESPONSE_CANCELADO);
            expect($result->estatusCancelacion)->toBe('Cancelado sin aceptación');
        });

        test('helper activo es false', function () use ($RESPONSE_CANCELADO) {
            $result = Soap::parseSoapResponse($RESPONSE_CANCELADO);
            expect($result->activo)->toBeFalse();
        });

        test('helper cancelado es true', function () use ($RESPONSE_CANCELADO) {
            $result = Soap::parseSoapResponse($RESPONSE_CANCELADO);
            expect($result->cancelado)->toBeTrue();
        });

        test('helper noEncontrado es false', function () use ($RESPONSE_CANCELADO) {
            $result = Soap::parseSoapResponse($RESPONSE_CANCELADO);
            expect($result->noEncontrado)->toBeFalse();
        });
    });

    describe('CFDI No Encontrado', function () use ($RESPONSE_NO_ENCONTRADO) {

        test('extrae el estado como No Encontrado', function () use ($RESPONSE_NO_ENCONTRADO) {
            $result = Soap::parseSoapResponse($RESPONSE_NO_ENCONTRADO);
            expect($result->estado)->toBe('No Encontrado');
        });

        test('helper activo es false', function () use ($RESPONSE_NO_ENCONTRADO) {
            $result = Soap::parseSoapResponse($RESPONSE_NO_ENCONTRADO);
            expect($result->activo)->toBeFalse();
        });

        test('helper cancelado es false', function () use ($RESPONSE_NO_ENCONTRADO) {
            $result = Soap::parseSoapResponse($RESPONSE_NO_ENCONTRADO);
            expect($result->cancelado)->toBeFalse();
        });

        test('helper noEncontrado es true', function () use ($RESPONSE_NO_ENCONTRADO) {
            $result = Soap::parseSoapResponse($RESPONSE_NO_ENCONTRADO);
            expect($result->noEncontrado)->toBeTrue();
        });
    });

    describe('SOAP Fault', function () use ($RESPONSE_SOAP_FAULT) {

        test('lanza error cuando hay un SOAP Fault', function () use ($RESPONSE_SOAP_FAULT) {
            Soap::parseSoapResponse($RESPONSE_SOAP_FAULT);
        })->throws(\RuntimeException::class, 'SOAP Fault');

        test('incluye el mensaje de faultstring en el error', function () use ($RESPONSE_SOAP_FAULT) {
            Soap::parseSoapResponse($RESPONSE_SOAP_FAULT);
        })->throws(\RuntimeException::class, 'Error en la peticion');
    });
});
