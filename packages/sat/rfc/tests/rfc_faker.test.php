<?php

use Cfdi\Rfc\RfcValidator;
use Cfdi\Rfc\Rfc;
use Cfdi\Rfc\RfcFaker;

describe('RfcFaker::persona()', function () {

    test('genera un RFC de 13 caracteres', function () {
        $rfc = RfcFaker::persona();
        expect(strlen($rfc))->toBe(13);
    });

    test('genera un RFC que pasa la validacion de validate()', function () {
        $rfc = RfcFaker::persona();
        $result = RfcValidator::validate($rfc);
        expect($result['isValid'])->toBeTrue();
    });

    test('genera un RFC reconocido como persona fisica por Rfc', function () {
        $rfcStr = RfcFaker::persona();
        $rfc = Rfc::of($rfcStr);
        expect($rfc->isFisica())->toBeTrue();
        expect($rfc->isMoral())->toBeFalse();
    });

    test('genera un RFC que no es generico ni extranjero', function () {
        $rfc = Rfc::of(RfcFaker::persona());
        expect($rfc->isGeneric())->toBeFalse();
        expect($rfc->isForeign())->toBeFalse();
    });

    test('genera RFCs distintos en multiples llamadas', function () {
        $rfcs = [];
        for ($i = 0; $i < 10; $i++) {
            $rfcs[] = RfcFaker::persona();
        }
        expect(count(array_unique($rfcs)))->toBeGreaterThan(1);
    });

    test('el tipo retornado por validate() es "person"', function () {
        $rfc = RfcFaker::persona();
        expect(RfcValidator::validate($rfc)['type'])->toBe('person');
    });

    test('genera multiples RFCs validos consecutivos', function () {
        for ($i = 0; $i < 20; $i++) {
            $rfc = RfcFaker::persona();
            $result = RfcValidator::validate($rfc);
            expect($result['isValid'])->toBeTrue();
        }
    });
});

describe('RfcFaker::moral()', function () {

    test('genera un RFC de 12 caracteres', function () {
        $rfc = RfcFaker::moral();
        expect(strlen($rfc))->toBe(12);
    });

    test('genera un RFC que pasa la validacion de validate()', function () {
        $rfc = RfcFaker::moral();
        $result = RfcValidator::validate($rfc);
        expect($result['isValid'])->toBeTrue();
    });

    test('genera un RFC reconocido como persona moral por Rfc', function () {
        $rfcStr = RfcFaker::moral();
        $rfc = Rfc::of($rfcStr);
        expect($rfc->isMoral())->toBeTrue();
        expect($rfc->isFisica())->toBeFalse();
    });

    test('el tipo retornado por validate() es "company"', function () {
        $rfc = RfcFaker::moral();
        expect(RfcValidator::validate($rfc)['type'])->toBe('company');
    });

    test('genera multiples RFCs validos consecutivos', function () {
        for ($i = 0; $i < 20; $i++) {
            $rfc = RfcFaker::moral();
            $result = RfcValidator::validate($rfc);
            expect($result['isValid'])->toBeTrue();
        }
    });

    test('genera RFCs distintos en multiples llamadas', function () {
        $rfcs = [];
        for ($i = 0; $i < 10; $i++) {
            $rfcs[] = RfcFaker::moral();
        }
        expect(count(array_unique($rfcs)))->toBeGreaterThan(1);
    });
});

describe('diferencias entre persona() y moral()', function () {

    test('persona() y moral() generan longitudes distintas', function () {
        expect(strlen(RfcFaker::persona()))->toBe(13);
        expect(strlen(RfcFaker::moral()))->toBe(12);
    });

    test('los RFCs generados son strings mayusculas', function () {
        $persona = RfcFaker::persona();
        $moral = RfcFaker::moral();
        expect($persona)->toBe(strtoupper($persona));
        expect($moral)->toBe(strtoupper($moral));
    });

    test('los RFCs generados solo contienen caracteres validos del SAT', function () {
        $rfcRegex = '/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/';
        expect(RfcFaker::persona())->toMatch($rfcRegex);
        expect(RfcFaker::moral())->toMatch($rfcRegex);
    });
});
