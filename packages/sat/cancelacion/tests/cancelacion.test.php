<?php

declare(strict_types=1);

use Sat\Cancelacion\AceptacionRechazoParams;
use Sat\Cancelacion\CancelacionParams;
use Sat\Cancelacion\EstatusCancelacion;
use Sat\Cancelacion\MotivoCancelacion;
use Sat\Cancelacion\RespuestaAceptacionRechazo;
use Sat\Cancelacion\Soap\AceptacionRechazo;
use Sat\Cancelacion\Soap\Cancelar;

const CANCEL_TOKEN = '2024-01-01T00:00:00Z';
const CANCEL_CERT = 'MIIFbase64certdata==';
const CANCEL_SIG = 'base64signaturevalue==';
const CANCEL_UUID = 'a3d08a33-d0d8-4f36-a857-ab4b2a5edc7c';
const CANCEL_RFC = 'AAA010101AAA';
const CANCEL_SERIAL = '01020304AABB';

describe('MotivoCancelacion', function () {
    test('usa los códigos del SAT', function () {
        expect(MotivoCancelacion::ConRelacion->value)->toBe('01');
        expect(MotivoCancelacion::SinRelacion->value)->toBe('02');
        expect(MotivoCancelacion::NoOperacion->value)->toBe('03');
        expect(MotivoCancelacion::FacturaGlobal->value)->toBe('04');
    });
});

describe('EstatusCancelacion', function () {
    test('expone los valores del modelo Node', function () {
        expect(EstatusCancelacion::EnProceso->value)->toBe('EnProceso');
        expect(EstatusCancelacion::Cancelado->value)->toBe('Cancelado');
        expect(EstatusCancelacion::CancelacionRechazada->value)->toBe('Rechazada');
        expect(EstatusCancelacion::Plazo->value)->toBe('Plazo');
    });
});

describe('buildCancelacionXml', function () {
    test('incluye RFC, UUID, motivo y firma', function () {
        $params = new CancelacionParams(
            uuid: CANCEL_UUID,
            motivo: MotivoCancelacion::SinRelacion,
            rfcEmisor: CANCEL_RFC,
        );
        $xml = Cancelar::buildCancelacionXml(
            $params,
            CANCEL_RFC,
            '2024-06-01T12:00:00',
            CANCEL_CERT,
            CANCEL_SIG,
            CANCEL_SERIAL,
        );
        expect($xml)->toContain('xmlns="http://cancelacfd.sat.gob.mx"');
        expect($xml)->toContain('RfcEmisor="' . CANCEL_RFC . '"');
        expect($xml)->toContain('Fecha="2024-06-01T12:00:00"');
        expect($xml)->toContain('UUID="' . CANCEL_UUID . '"');
        expect($xml)->toContain('Motivo="02"');
        expect($xml)->toContain('<SignatureValue>' . CANCEL_SIG . '</SignatureValue>');
        expect($xml)->toContain('<X509SerialNumber>' . CANCEL_SERIAL . '</X509SerialNumber>');
        expect($xml)->toContain('<X509Certificate>' . CANCEL_CERT . '</X509Certificate>');
    });

    test('agrega FolioSustitucion solo con motivo 01 y folio', function () {
        $params = new CancelacionParams(
            uuid: CANCEL_UUID,
            motivo: MotivoCancelacion::ConRelacion,
            folioSustitucion: 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
        );
        $xml = Cancelar::buildCancelacionXml(
            $params,
            CANCEL_RFC,
            '2024-06-01T12:00:00',
            CANCEL_CERT,
            CANCEL_SIG,
            CANCEL_SERIAL,
        );
        expect($xml)->toContain('FolioSustitucion="bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb"');
    });
});

describe('buildCancelarRequest', function () {
    test('escapa el XML embebido en Cancelacion', function () {
        $inner = '<Cancelacion><x>a&b</x></Cancelacion>';
        $outer = Cancelar::buildCancelarRequest($inner, CANCEL_TOKEN, CANCEL_CERT, CANCEL_SIG);
        expect($outer)->toContain('&lt;Cancelacion&gt;');
        expect($outer)->toContain('a&amp;b');
        expect($outer)->toContain('CancelaCFD xmlns="http://tempuri.org/"');
        expect($outer)->toContain(CANCEL_TOKEN);
        expect($outer)->toContain(CANCEL_CERT);
    });
});

$RESPONSE_CANCELAR_OK = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <CancelaCFDResponse xmlns="http://tempuri.org/">
      <CancelaCFDResult>
        <Folio UUID="' . CANCEL_UUID . '" EstatusUUID="201" CodEstatus="OK" Mensaje="Listo"/>
      </CancelaCFDResult>
    </CancelaCFDResponse>
  </s:Body>
</s:Envelope>';

$RESPONSE_CANCELAR_FAULT = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <s:Fault>
      <faultcode>s:Client</faultcode>
      <faultstring>Token invalido</faultstring>
    </s:Fault>
  </s:Body>
</s:Envelope>';

describe('parseCancelarResponse', function () use ($RESPONSE_CANCELAR_OK, $RESPONSE_CANCELAR_FAULT) {
    test('mapea EstatusUUID 201 a Cancelado', function () use ($RESPONSE_CANCELAR_OK) {
        $r = Cancelar::parseCancelarResponse($RESPONSE_CANCELAR_OK);
        expect($r->uuid)->toBe(CANCEL_UUID);
        expect($r->estatus)->toBe(EstatusCancelacion::Cancelado);
    });

    test('lanza RuntimeException en SOAP Fault', function () use ($RESPONSE_CANCELAR_FAULT) {
        expect(fn () => Cancelar::parseCancelarResponse($RESPONSE_CANCELAR_FAULT))
            ->toThrow(RuntimeException::class, 'SOAP Fault');
        expect(fn () => Cancelar::parseCancelarResponse($RESPONSE_CANCELAR_FAULT))
            ->toThrow(RuntimeException::class, 'Token invalido');
    });
});

describe('AceptacionRechazo SOAP', function () {
    test('buildAceptacionRechazoRequest incluye elementos esperados', function () {
        $params = new AceptacionRechazoParams(
            rfcReceptor: CANCEL_RFC,
            uuid: CANCEL_UUID,
            respuesta: RespuestaAceptacionRechazo::Aceptacion,
        );
        $xml = AceptacionRechazo::buildAceptacionRechazoRequest(
            $params,
            CANCEL_TOKEN,
            CANCEL_CERT,
            CANCEL_SIG,
            '2024-06-01T12:00:00',
        );
        expect($xml)->toContain('<RfcReceptor>' . CANCEL_RFC . '</RfcReceptor>');
        expect($xml)->toContain('<UUID>' . CANCEL_UUID . '</UUID>');
        expect($xml)->toContain('<Respuesta>Aceptacion</Respuesta>');
        expect($xml)->toContain('<ProcesarRespuesta xmlns="http://cancelacfd.sat.gob.mx/">');
        expect($xml)->toContain('<SignatureValue>' . CANCEL_SIG . '</SignatureValue>');
    });

    test('buildConsultaPendientesRequest incluye RfcReceptor', function () {
        $xml = AceptacionRechazo::buildConsultaPendientesRequest(
            CANCEL_RFC,
            CANCEL_TOKEN,
            CANCEL_CERT,
            CANCEL_SIG,
        );
        expect($xml)->toContain('<ConsultaPendientes xmlns="http://cancelacfd.sat.gob.mx/">');
        expect($xml)->toContain('<RfcReceptor>' . CANCEL_RFC . '</RfcReceptor>');
    });
});

$RESPONSE_AR_OK = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <ProcesarRespuestaResponse xmlns="http://cancelacfd.sat.gob.mx/">
      <UUID>' . CANCEL_UUID . '</UUID>
      <CodEstatus>5000</CodEstatus>
      <Mensaje>Aceptado</Mensaje>
    </ProcesarRespuestaResponse>
  </s:Body>
</s:Envelope>';

$RESPONSE_PENDIENTES = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <ConsultaPendientesResponse>
      <Pendiente>
        <UUID>' . CANCEL_UUID . '</UUID>
        <RfcEmisor>BBB020202BBB</RfcEmisor>
        <FechaSolicitud>2024-05-01T10:00:00</FechaSolicitud>
      </Pendiente>
    </ConsultaPendientesResponse>
  </s:Body>
</s:Envelope>';

describe('parseAceptacionRechazoResponse', function () use ($RESPONSE_AR_OK) {
    test('extrae uuid, codigo y mensaje', function () use ($RESPONSE_AR_OK) {
        $r = AceptacionRechazo::parseAceptacionRechazoResponse($RESPONSE_AR_OK);
        expect($r->uuid)->toBe(CANCEL_UUID);
        expect($r->codEstatus)->toBe('5000');
        expect($r->mensaje)->toBe('Aceptado');
    });
});

describe('parsePendientesResponse', function () use ($RESPONSE_PENDIENTES) {
    test('retorna lista de pendientes', function () use ($RESPONSE_PENDIENTES) {
        $list = AceptacionRechazo::parsePendientesResponse($RESPONSE_PENDIENTES);
        expect($list)->toHaveCount(1);
        expect($list[0]->uuid)->toBe(CANCEL_UUID);
        expect($list[0]->rfcEmisor)->toBe('BBB020202BBB');
        expect($list[0]->fechaSolicitud)->toBe('2024-05-01T10:00:00');
    });
});
