<?php

declare(strict_types=1);

use Sat\Pacs\PacConfig;
use Sat\Pacs\PacProviderType;
use Sat\Pacs\Providers\FinkokProvider;
use Sat\Pacs\TimbradoRequest;
use Sat\Pacs\TimbradoResult;

describe('PacProviderType', function () {
    test('casos finkok y custom', function () {
        expect(PacProviderType::Finkok->value)->toBe('finkok');
        expect(PacProviderType::Custom->value)->toBe('custom');
    });
});

describe('PacConfig', function () {
    test('conserva url usuario y contraseña', function () {
        $c = new PacConfig('https://facturacion.finkok.com', 'user1', 'secret', sandbox: false);
        expect($c->url)->toBe('https://facturacion.finkok.com');
        expect($c->user)->toBe('user1');
        expect($c->password)->toBe('secret');
        expect($c->sandbox)->toBeFalse();
    });
});

describe('TimbradoRequest', function () {
    test('envuelve el XML del CFDI', function () {
        $r = new TimbradoRequest('<cfdi:Comprobante/>');
        expect($r->xml)->toBe('<cfdi:Comprobante/>');
    });
});

describe('FinkokProvider', function () {
    test('escapeXml coincide con el escape usado en SOAP', function () {
        $m = new ReflectionMethod(FinkokProvider::class, 'escapeXml');
        $m->setAccessible(true);
        expect($m->invoke(null, 'a&b<c>"'))->toBe('a&amp;b&lt;c&gt;&quot;');
    });

    test('extractTimbreFields lee atributos del TimbreFiscalDigital', function () {
        $xml = '<root xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital">'
            . '<tfd:TimbreFiscalDigital UUID="6c2c550c-4b43-4b56-b747-76f0d134b9b2" '
            . 'FechaTimbrado="2024-01-15T10:00:00" SelloCFD="s1" SelloSAT="s2" '
            . 'NoCertificadoSAT="30001000000400002434" CadenaOriginal="cad"/>'
            . '</root>';

        $m = new ReflectionMethod(FinkokProvider::class, 'extractTimbreFields');
        $m->setAccessible(true);
        /** @var array{uuid: string, fechaTimbrado: string, selloCFD: string, selloSAT: string, noCertificadoSAT: string, cadenaOriginalSAT: string} $fields */
        $fields = $m->invoke(null, $xml);

        expect($fields['uuid'])->toBe('6c2c550c-4b43-4b56-b747-76f0d134b9b2');
        expect($fields['fechaTimbrado'])->toBe('2024-01-15T10:00:00');
        expect($fields['selloCFD'])->toBe('s1');
        expect($fields['selloSAT'])->toBe('s2');
        expect($fields['noCertificadoSAT'])->toBe('30001000000400002434');
        expect($fields['cadenaOriginalSAT'])->toBe('cad');
    });

    test('timbrar lanza si la respuesta SOAP indica error', function () {
        $provider = new class (new PacConfig(null, 'u', 'p')) extends FinkokProvider {
            protected function postSoap(string $url, string $xml): string
            {
                return '<?xml version="1.0"?><s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">'
                    . '<s:Body><MensajeIncidencia>Certificado invalido</MensajeIncidencia></s:Body></s:Envelope>';
            }
        };

        $provider->timbrar(new TimbradoRequest('<cfdi/>'));
    })->throws(RuntimeException::class, 'Certificado invalido');
});

describe('TimbradoResult', function () {
    test('agrupa datos del timbrado', function () {
        $t = new TimbradoResult(
            uuid: 'u1',
            fecha: '2024-01-01',
            selloCFD: 'a',
            selloSAT: 'b',
            noCertificadoSAT: 'c',
            cadenaOriginalSAT: 'd',
            xml: '<xml/>',
        );
        expect($t->uuid)->toBe('u1');
        expect($t->fecha)->toBe('2024-01-01');
        expect($t->xml)->toBe('<xml/>');
    });
});
