<?php

use Cfdi\Descarga\EstadoSolicitud;
use Cfdi\Descarga\SolicitudParams;
use Cfdi\Descarga\TipoDescarga;
use Cfdi\Descarga\TipoSolicitud;
use Cfdi\Descarga\Soap\Descargar;
use Cfdi\Descarga\Soap\Signer;
use Cfdi\Descarga\Soap\Solicitar;
use Cfdi\Descarga\Soap\Verificar;

const SOAP_RFC = 'AAA010101AAA';
const SOAP_TOKEN = '2024-01-01T00:00:00Z';
const SOAP_CERT = 'MIIFbase64certdata==';
const SOAP_SIGNATURE = 'base64signaturevalue==';
const SOAP_ID_SOLICITUD = 'a3d08a33-d0d8-4f36-a857-ab4b2a5edc7c';
const SOAP_ID_PAQUETE = 'a3d08a33-d0d8-4f36-a857-ab4b2a5edc7c_01';

$SOAP_SOLICITUD_PARAMS = new SolicitudParams(
    rfcSolicitante: SOAP_RFC,
    fechaInicio: '2024-01-01',
    fechaFin: '2024-01-31',
    tipoSolicitud: TipoSolicitud::CFDI,
    tipoDescarga: TipoDescarga::Emitidos,
);

describe('canonicalize', function () {
    test('elimina la declaracion XML', function () {
        expect(Signer::canonicalize('<?xml version="1.0" encoding="utf-8"?><root/>'))->toBe('<root/>');
    });

    test('retorna el mismo string si no tiene declaracion XML', function () {
        expect(Signer::canonicalize('<root><child/></root>'))->toBe('<root><child/></root>');
    });

    test('elimina espacios al inicio y final', function () {
        expect(Signer::canonicalize('  <root/>  '))->toBe('<root/>');
    });
});

describe('digestSha256', function () {
    test('retorna un string Base64 valido', function () {
        $digest = Signer::digestSha256('hola mundo');
        expect($digest)->toMatch('/^[A-Za-z0-9+\/]+=*$/');
        expect(strlen($digest))->toBeGreaterThan(20);
    });

    test('produce el mismo digest para la misma entrada', function () {
        $a = Signer::digestSha256('contenido de prueba');
        $b = Signer::digestSha256('contenido de prueba');
        expect($a)->toBe($b);
    });

    test('produce digests diferentes para entradas diferentes', function () {
        $a = Signer::digestSha256('contenido A');
        $b = Signer::digestSha256('contenido B');
        expect($a)->not->toBe($b);
    });
});

describe('buildSolicitarRequest', function () use ($SOAP_SOLICITUD_PARAMS) {
    test('genera un envelope SOAP valido con los namespaces correctos', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"');
        expect($xml)->toContain('xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx/"');
        expect($xml)->toContain('s:Envelope');
        expect($xml)->toContain('s:Body');
    });

    test('incluye el elemento SolicitaDescarga', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('des:SolicitaDescarga');
        expect($xml)->toContain('des:solicitud');
    });

    test('incluye la fecha inicial en formato ISO', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('FechaInicial="2024-01-01T00:00:00"');
    });

    test('incluye la fecha final en formato ISO', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('FechaFinal="2024-01-31T23:59:59"');
    });

    test('incluye el RFC solicitante', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('RfcSolicitante="' . SOAP_RFC . '"');
    });

    test('incluye el tipo de solicitud CFDI', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('TipoSolicitud="CFDI"');
    });

    test('incluye el tipo de solicitud Metadata cuando se especifica', function () use ($SOAP_SOLICITUD_PARAMS) {
        $params = new SolicitudParams(
            rfcSolicitante: SOAP_RFC,
            fechaInicio: '2024-01-01',
            fechaFin: '2024-01-31',
            tipoSolicitud: TipoSolicitud::Metadata,
            tipoDescarga: TipoDescarga::Emitidos,
        );
        $xml = Solicitar::buildSolicitarRequest(
            $params,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('TipoSolicitud="Metadata"');
    });

    test('incluye RfcEmisor para descarga de emitidos', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('RfcEmisor="' . SOAP_RFC . '"');
    });

    test('incluye RfcReceptor para descarga de recibidos', function () use ($SOAP_SOLICITUD_PARAMS) {
        $params = new SolicitudParams(
            rfcSolicitante: SOAP_RFC,
            fechaInicio: '2024-01-01',
            fechaFin: '2024-01-31',
            tipoSolicitud: TipoSolicitud::CFDI,
            tipoDescarga: TipoDescarga::Recibidos,
            rfcReceptor: 'BBB020202BBB',
        );
        $xml = Solicitar::buildSolicitarRequest(
            $params,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('RfcReceptor="BBB020202BBB"');
    });

    test('incluye el certificado en el header de seguridad', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain(SOAP_CERT);
    });

    test('incluye el valor de firma', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain(SOAP_SIGNATURE);
    });

    test('incluye el token en el timestamp del header', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain(SOAP_TOKEN);
    });

    test('incluye los algoritmos de firma RSA-SHA256', function () use ($SOAP_SOLICITUD_PARAMS) {
        $xml = Solicitar::buildSolicitarRequest(
            $SOAP_SOLICITUD_PARAMS,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
    });
});

$RESPONSE_SOLICITAR_OK = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <SolicitaDescargaResponse xmlns="http://DescargaMasivaTerceros.sat.gob.mx/">
      <SolicitaDescargaResult CodEstatus="5000"
                               IdSolicitud="' . SOAP_ID_SOLICITUD . '"
                               Mensaje="Solicitud Aceptada"/>
    </SolicitaDescargaResponse>
  </s:Body>
</s:Envelope>';

$RESPONSE_SOLICITAR_FAULT = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <s:Fault>
      <faultcode>s:Client</faultcode>
      <faultstring>RFC no valido</faultstring>
    </s:Fault>
  </s:Body>
</s:Envelope>';

describe('parseSolicitarResponse', function () use ($RESPONSE_SOLICITAR_OK, $RESPONSE_SOLICITAR_FAULT) {
    test('extrae el IdSolicitud de la respuesta exitosa', function () use ($RESPONSE_SOLICITAR_OK) {
        $result = Solicitar::parseSolicitarResponse($RESPONSE_SOLICITAR_OK);
        expect($result->idSolicitud)->toBe(SOAP_ID_SOLICITUD);
    });

    test('extrae el CodEstatus de la respuesta exitosa', function () use ($RESPONSE_SOLICITAR_OK) {
        $result = Solicitar::parseSolicitarResponse($RESPONSE_SOLICITAR_OK);
        expect($result->codEstatus)->toBe('5000');
    });

    test('extrae el Mensaje de la respuesta exitosa', function () use ($RESPONSE_SOLICITAR_OK) {
        $result = Solicitar::parseSolicitarResponse($RESPONSE_SOLICITAR_OK);
        expect($result->mensaje)->toBe('Solicitud Aceptada');
    });

    test('lanza Error cuando la respuesta contiene un SOAP Fault', function () use ($RESPONSE_SOLICITAR_FAULT) {
        expect(fn () => Solicitar::parseSolicitarResponse($RESPONSE_SOLICITAR_FAULT))
            ->toThrow(\RuntimeException::class, 'SOAP Fault');
    });

    test('incluye el mensaje del SOAP Fault en el error', function () use ($RESPONSE_SOLICITAR_FAULT) {
        expect(fn () => Solicitar::parseSolicitarResponse($RESPONSE_SOLICITAR_FAULT))
            ->toThrow(\RuntimeException::class, 'RFC no valido');
    });
});

describe('buildVerificarRequest', function () {
    test('genera un envelope SOAP valido', function () {
        $xml = Verificar::buildVerificarRequest(
            SOAP_ID_SOLICITUD,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('s:Envelope');
        expect($xml)->toContain('s:Body');
        expect($xml)->toContain('des:VerificaSolicitudDescarga');
    });

    test('incluye el IdSolicitud en el body', function () {
        $xml = Verificar::buildVerificarRequest(
            SOAP_ID_SOLICITUD,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('IdSolicitud="' . SOAP_ID_SOLICITUD . '"');
    });

    test('incluye el RFC solicitante', function () {
        $xml = Verificar::buildVerificarRequest(
            SOAP_ID_SOLICITUD,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('RfcSolicitante="' . SOAP_RFC . '"');
    });

    test('incluye el certificado', function () {
        $xml = Verificar::buildVerificarRequest(
            SOAP_ID_SOLICITUD,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain(SOAP_CERT);
    });

    test('incluye el valor de firma', function () {
        $xml = Verificar::buildVerificarRequest(
            SOAP_ID_SOLICITUD,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain(SOAP_SIGNATURE);
    });
});

$RESPONSE_VERIFICAR_TERMINADA = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <VerificaSolicitudDescargaResponse xmlns="http://DescargaMasivaTerceros.sat.gob.mx/">
      <VerificaSolicitudDescargaResult CodEstatus="5000"
                                        EstadoSolicitud="3"
                                        NumeroCFDIs="150"
                                        Mensaje="Solicitud Terminada">
        <IdsPaquetes>' . SOAP_ID_PAQUETE . '</IdsPaquetes>
      </VerificaSolicitudDescargaResult>
    </VerificaSolicitudDescargaResponse>
  </s:Body>
</s:Envelope>';

$RESPONSE_VERIFICAR_EN_PROCESO = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <VerificaSolicitudDescargaResponse xmlns="http://DescargaMasivaTerceros.sat.gob.mx/">
      <VerificaSolicitudDescargaResult CodEstatus="5001"
                                        EstadoSolicitud="2"
                                        NumeroCFDIs="0"
                                        Mensaje="En proceso"/>
    </VerificaSolicitudDescargaResponse>
  </s:Body>
</s:Envelope>';

$RESPONSE_VERIFICAR_FAULT = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <s:Fault>
      <faultcode>s:Client</faultcode>
      <faultstring>Token invalido</faultstring>
    </s:Fault>
  </s:Body>
</s:Envelope>';

describe('parseVerificarResponse', function () use (
    $RESPONSE_VERIFICAR_TERMINADA,
    $RESPONSE_VERIFICAR_EN_PROCESO,
    $RESPONSE_VERIFICAR_FAULT
) {
    describe('solicitud terminada', function () use ($RESPONSE_VERIFICAR_TERMINADA) {
        test('extrae el estado Terminada (3)', function () use ($RESPONSE_VERIFICAR_TERMINADA) {
            $result = Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_TERMINADA);
            expect($result->estado)->toBe(EstadoSolicitud::Terminada);
        });

        test('extrae la descripcion del estado', function () use ($RESPONSE_VERIFICAR_TERMINADA) {
            $result = Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_TERMINADA);
            expect($result->estadoDescripcion)->toBe('Terminada');
        });

        test('extrae el CodEstatus', function () use ($RESPONSE_VERIFICAR_TERMINADA) {
            $result = Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_TERMINADA);
            expect($result->codEstatus)->toBe('5000');
        });

        test('extrae el Mensaje', function () use ($RESPONSE_VERIFICAR_TERMINADA) {
            $result = Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_TERMINADA);
            expect($result->mensaje)->toBe('Solicitud Terminada');
        });

        test('extrae el NumeroCFDIs', function () use ($RESPONSE_VERIFICAR_TERMINADA) {
            $result = Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_TERMINADA);
            expect($result->numeroCfdis)->toBe(150);
        });

        test('extrae los IdsPaquetes', function () use ($RESPONSE_VERIFICAR_TERMINADA) {
            $result = Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_TERMINADA);
            expect($result->idsPaquetes)->toContain(SOAP_ID_PAQUETE);
        });
    });

    describe('solicitud en proceso', function () use ($RESPONSE_VERIFICAR_EN_PROCESO) {
        test('extrae el estado EnProceso (2)', function () use ($RESPONSE_VERIFICAR_EN_PROCESO) {
            $result = Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_EN_PROCESO);
            expect($result->estado)->toBe(EstadoSolicitud::EnProceso);
        });

        test('la lista de paquetes esta vacia', function () use ($RESPONSE_VERIFICAR_EN_PROCESO) {
            $result = Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_EN_PROCESO);
            expect($result->idsPaquetes)->toHaveCount(0);
        });

        test('numeroCfdis es 0 mientras esta en proceso', function () use ($RESPONSE_VERIFICAR_EN_PROCESO) {
            $result = Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_EN_PROCESO);
            expect($result->numeroCfdis)->toBe(0);
        });
    });

    describe('SOAP Fault', function () use ($RESPONSE_VERIFICAR_FAULT) {
        test('lanza Error cuando la respuesta contiene un SOAP Fault', function () use ($RESPONSE_VERIFICAR_FAULT) {
            expect(fn () => Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_FAULT))
                ->toThrow(\RuntimeException::class, 'SOAP Fault');
        });

        test('incluye el mensaje del fault', function () use ($RESPONSE_VERIFICAR_FAULT) {
            expect(fn () => Verificar::parseVerificarResponse($RESPONSE_VERIFICAR_FAULT))
                ->toThrow(\RuntimeException::class, 'Token invalido');
        });
    });
});

describe('buildDescargarRequest', function () {
    test('genera un envelope SOAP valido', function () {
        $xml = Descargar::buildDescargarRequest(
            SOAP_ID_PAQUETE,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('s:Envelope');
        expect($xml)->toContain('s:Body');
        expect($xml)->toContain('des:PeticionDescargaMasivaTercerosEntrada');
    });

    test('incluye el IdPaquete en el body', function () {
        $xml = Descargar::buildDescargarRequest(
            SOAP_ID_PAQUETE,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('IdPaquete="' . SOAP_ID_PAQUETE . '"');
    });

    test('incluye el RFC solicitante', function () {
        $xml = Descargar::buildDescargarRequest(
            SOAP_ID_PAQUETE,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('RfcSolicitante="' . SOAP_RFC . '"');
    });

    test('incluye el certificado', function () {
        $xml = Descargar::buildDescargarRequest(
            SOAP_ID_PAQUETE,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain(SOAP_CERT);
    });

    test('incluye el valor de firma', function () {
        $xml = Descargar::buildDescargarRequest(
            SOAP_ID_PAQUETE,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain(SOAP_SIGNATURE);
    });

    test('incluye el namespace de descarga masiva', function () {
        $xml = Descargar::buildDescargarRequest(
            SOAP_ID_PAQUETE,
            SOAP_RFC,
            SOAP_TOKEN,
            SOAP_CERT,
            SOAP_SIGNATURE
        );
        expect($xml)->toContain('xmlns:des="http://DescargaMasivaTerceros.sat.gob.mx/"');
    });
});

$ZIP_CONTENT = "PK\x03\x04fake zip content";
$ZIP_B64 = base64_encode($ZIP_CONTENT);

$RESPONSE_DESCARGAR_OK = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <PeticionDescargaMasivaTercerosSalida xmlns="http://DescargaMasivaTerceros.sat.gob.mx/">
      <Paquete>' . $ZIP_B64 . '</Paquete>
    </PeticionDescargaMasivaTercerosSalida>
  </s:Body>
</s:Envelope>';

$RESPONSE_DESCARGAR_FAULT = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <s:Fault>
      <faultcode>s:Client</faultcode>
      <faultstring>Paquete no encontrado</faultstring>
    </s:Fault>
  </s:Body>
</s:Envelope>';

$RESPONSE_DESCARGAR_SIN_PAQUETE = '<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <PeticionDescargaMasivaTercerosSalida xmlns="http://DescargaMasivaTerceros.sat.gob.mx/">
    </PeticionDescargaMasivaTercerosSalida>
  </s:Body>
</s:Envelope>';

describe('parseDescargarResponse', function () use (
    $RESPONSE_DESCARGAR_OK,
    $ZIP_CONTENT,
    $RESPONSE_DESCARGAR_FAULT,
    $RESPONSE_DESCARGAR_SIN_PAQUETE
) {
    test('retorna el contenido del ZIP como string binario', function () use ($RESPONSE_DESCARGAR_OK) {
        $result = Descargar::parseDescargarResponse($RESPONSE_DESCARGAR_OK);
        expect(is_string($result))->toBeTrue();
    });

    test('el string contiene el contenido correcto decodificado de Base64', function () use ($RESPONSE_DESCARGAR_OK, $ZIP_CONTENT) {
        $result = Descargar::parseDescargarResponse($RESPONSE_DESCARGAR_OK);
        expect($result)->toBe($ZIP_CONTENT);
    });

    test('lanza Error cuando la respuesta contiene un SOAP Fault', function () use ($RESPONSE_DESCARGAR_FAULT) {
        expect(fn () => Descargar::parseDescargarResponse($RESPONSE_DESCARGAR_FAULT))
            ->toThrow(\RuntimeException::class, 'SOAP Fault');
    });

    test('incluye el mensaje del fault en el error', function () use ($RESPONSE_DESCARGAR_FAULT) {
        expect(fn () => Descargar::parseDescargarResponse($RESPONSE_DESCARGAR_FAULT))
            ->toThrow(\RuntimeException::class, 'Paquete no encontrado');
    });

    test('lanza Error cuando no hay elemento Paquete en la respuesta', function () use ($RESPONSE_DESCARGAR_SIN_PAQUETE) {
        expect(fn () => Descargar::parseDescargarResponse($RESPONSE_DESCARGAR_SIN_PAQUETE))
            ->toThrow(\RuntimeException::class, 'no contiene el elemento Paquete');
    });
});
