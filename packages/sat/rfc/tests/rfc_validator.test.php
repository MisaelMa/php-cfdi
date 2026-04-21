<?php

use Cfdi\Rfc\RfcValidator;

$RFC_PERSONA_FISICA = 'GODE561231GR8';
$RFC_PERSONA_MORAL = 'BMC860829IF3';

describe('validate()', function () use ($RFC_PERSONA_FISICA, $RFC_PERSONA_MORAL) {

    describe('RFCs validos', function () use ($RFC_PERSONA_FISICA, $RFC_PERSONA_MORAL) {

        test('acepta RFC valido de persona fisica (13 chars)', function () use ($RFC_PERSONA_FISICA) {
            $result = RfcValidator::validate($RFC_PERSONA_FISICA);
            expect($result['isValid'])->toBeTrue();
            expect($result['type'])->toBe('person');
            expect($result['rfc'])->toBe($RFC_PERSONA_FISICA);
        });

        test('acepta RFC valido de persona moral (12 chars)', function () use ($RFC_PERSONA_MORAL) {
            $result = RfcValidator::validate($RFC_PERSONA_MORAL);
            expect($result['isValid'])->toBeTrue();
            expect($result['type'])->toBe('company');
            expect($result['rfc'])->toBe($RFC_PERSONA_MORAL);
        });

        test('normaliza el input — minusculas y espacios extra', function () use ($RFC_PERSONA_FISICA) {
            $result = RfcValidator::validate('  gode561231gr8  ');
            expect($result['isValid'])->toBeTrue();
            expect($result['rfc'])->toBe($RFC_PERSONA_FISICA);
        });

        test('siempre retorna el array con las propiedades isValid, type y rfc aunque sea invalido', function () {
            $result = RfcValidator::validate('invalido');
            expect($result)->toHaveKeys(['isValid', 'type', 'rfc']);
        });
    });

    describe('RFCs invalidos', function () use ($RFC_PERSONA_FISICA) {

        test('rechaza cadena vacia', function () {
            $result = RfcValidator::validate('');
            expect($result['isValid'])->toBeFalse();
        });

        test('rechaza RFC demasiado corto', function () {
            expect(RfcValidator::validate('GOD56')['isValid'])->toBeFalse();
        });

        test('rechaza RFC demasiado largo', function () {
            expect(RfcValidator::validate('GODE5612311234GR8X')['isValid'])->toBeFalse();
        });

        test('rechaza RFC con mes imposible (13)', function () {
            expect(RfcValidator::validate('GODE561301GR8')['isValid'])->toBeFalse();
        });

        test('rechaza RFC con dia imposible (00)', function () {
            expect(RfcValidator::validate('GODE561200GR8')['isValid'])->toBeFalse();
        });

        test('rechaza RFC con digito verificador incorrecto', function () use ($RFC_PERSONA_FISICA) {
            $rfcMalo = substr($RFC_PERSONA_FISICA, 0, -1) . '9';
            expect(RfcValidator::validate($rfcMalo)['isValid'])->toBeFalse();
        });

        test('rechaza RFC con palabra prohibida en prefijo', function () {
            $result = RfcValidator::validate('BUEI010101XX0');
            expect($result['isValid'])->toBeFalse();
        });
    });
});

describe('getType()', function () use ($RFC_PERSONA_FISICA, $RFC_PERSONA_MORAL) {

    test('retorna "person" para RFC de 13 caracteres', function () use ($RFC_PERSONA_FISICA) {
        expect(RfcValidator::getType($RFC_PERSONA_FISICA))->toBe('person');
    });

    test('retorna "company" para RFC de 12 caracteres', function () use ($RFC_PERSONA_MORAL) {
        expect(RfcValidator::getType($RFC_PERSONA_MORAL))->toBe('company');
    });

    test('retorna "generic" para XAXX010101000', function () {
        expect(RfcValidator::getType('XAXX010101000'))->toBe('generic');
    });

    test('retorna "foreign" para XEXX010101000', function () {
        expect(RfcValidator::getType('XEXX010101000'))->toBe('foreign');
    });
});

describe('hasForbiddenWords()', function () use ($RFC_PERSONA_FISICA, $RFC_PERSONA_MORAL) {

    test('detecta palabras prohibidas al inicio del RFC', function () {
        expect(RfcValidator::hasForbiddenWords('BUEI010101XX0'))->toBeTrue();
        expect(RfcValidator::hasForbiddenWords('CACA010101XX0'))->toBeTrue();
        expect(RfcValidator::hasForbiddenWords('MEAR010101XX0'))->toBeTrue();
        expect(RfcValidator::hasForbiddenWords('PUTA010101XX0'))->toBeTrue();
    });

    test('no detecta palabra prohibida en RFC normal', function () use ($RFC_PERSONA_FISICA, $RFC_PERSONA_MORAL) {
        expect(RfcValidator::hasForbiddenWords($RFC_PERSONA_FISICA))->toBeFalse();
        expect(RfcValidator::hasForbiddenWords($RFC_PERSONA_MORAL))->toBeFalse();
    });

    test('no detecta palabra prohibida si el prefijo no coincide exactamente', function () {
        expect(RfcValidator::hasForbiddenWords('MULE010101XX0'))->toBeFalse();
    });
});
