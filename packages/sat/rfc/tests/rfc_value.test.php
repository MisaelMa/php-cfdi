<?php

use Cfdi\Rfc\Rfc;
use Cfdi\Rfc\InvalidRfcError;

$RFC_FISICA = 'GODE561231GR8';
$RFC_MORAL = 'BMC860829IF3';
$RFC_GENERICO = 'XAXX010101000';
$RFC_EXTRANJERO = 'XEXX010101000';

describe('Rfc::of()', function () use ($RFC_FISICA, $RFC_MORAL, $RFC_GENERICO, $RFC_EXTRANJERO) {

    test('crea instancia con RFC valido de persona fisica', function () use ($RFC_FISICA) {
        $rfc = Rfc::of($RFC_FISICA);
        expect($rfc->toString())->toBe($RFC_FISICA);
    });

    test('crea instancia con RFC valido de persona moral', function () use ($RFC_MORAL) {
        $rfc = Rfc::of($RFC_MORAL);
        expect($rfc->toString())->toBe($RFC_MORAL);
    });

    test('acepta RFC generico XAXX010101000 por definicion reglamentaria', function () use ($RFC_GENERICO) {
        $rfc = Rfc::of($RFC_GENERICO);
        expect($rfc->toString())->toBe($RFC_GENERICO);
    });

    test('acepta RFC para extranjeros XEXX010101000 por definicion reglamentaria', function () use ($RFC_EXTRANJERO) {
        $rfc = Rfc::of($RFC_EXTRANJERO);
        expect($rfc->toString())->toBe($RFC_EXTRANJERO);
    });

    test('normaliza el input a mayusculas y sin espacios', function () use ($RFC_FISICA) {
        $rfc = Rfc::of('  gode561231gr8  ');
        expect($rfc->toString())->toBe($RFC_FISICA);
    });

    test('lanza InvalidRfcError con RFC invalido', function () {
        Rfc::of('RFC_INVALIDO');
    })->throws(InvalidRfcError::class);

    test('el mensaje de error incluye el RFC proporcionado', function () {
        Rfc::of('MAL');
    })->throws(InvalidRfcError::class, "'MAL' is not a valid RFC");

    test('lanza InvalidRfcError con cadena vacia', function () {
        Rfc::of('');
    })->throws(InvalidRfcError::class);
});

describe('Rfc::parse()', function () use ($RFC_FISICA, $RFC_MORAL, $RFC_GENERICO, $RFC_EXTRANJERO) {

    test('retorna instancia Rfc con RFC valido de persona fisica', function () use ($RFC_FISICA) {
        $rfc = Rfc::parse($RFC_FISICA);
        expect($rfc)->not->toBeNull();
        expect($rfc->toString())->toBe($RFC_FISICA);
    });

    test('retorna instancia Rfc con RFC valido de persona moral', function () use ($RFC_MORAL) {
        $rfc = Rfc::parse($RFC_MORAL);
        expect($rfc)->not->toBeNull();
        expect($rfc->toString())->toBe($RFC_MORAL);
    });

    test('retorna null con RFC invalido', function () {
        expect(Rfc::parse('INVALIDO'))->toBeNull();
    });

    test('retorna null con cadena vacia', function () {
        expect(Rfc::parse(''))->toBeNull();
    });

    test('acepta RFC generico', function () use ($RFC_GENERICO) {
        expect(Rfc::parse($RFC_GENERICO))->not->toBeNull();
    });

    test('acepta RFC de extranjero', function () use ($RFC_EXTRANJERO) {
        expect(Rfc::parse($RFC_EXTRANJERO))->not->toBeNull();
    });
});

describe('Rfc::isValid()', function () use ($RFC_FISICA, $RFC_MORAL, $RFC_GENERICO, $RFC_EXTRANJERO) {

    test('retorna true para RFC de persona fisica valido', function () use ($RFC_FISICA) {
        expect(Rfc::isValid($RFC_FISICA))->toBeTrue();
    });

    test('retorna true para RFC de persona moral valido', function () use ($RFC_MORAL) {
        expect(Rfc::isValid($RFC_MORAL))->toBeTrue();
    });

    test('retorna true para RFC generico', function () use ($RFC_GENERICO) {
        expect(Rfc::isValid($RFC_GENERICO))->toBeTrue();
    });

    test('retorna true para RFC de extranjero', function () use ($RFC_EXTRANJERO) {
        expect(Rfc::isValid($RFC_EXTRANJERO))->toBeTrue();
    });

    test('retorna false para RFC con formato invalido', function () {
        expect(Rfc::isValid('INVALIDO'))->toBeFalse();
    });

    test('retorna false para RFC con digito verificador incorrecto', function () use ($RFC_FISICA) {
        $rfcMalo = substr($RFC_FISICA, 0, -1) . '9';
        expect(Rfc::isValid($rfcMalo))->toBeFalse();
    });
});

describe('isFisica()', function () use ($RFC_FISICA, $RFC_MORAL, $RFC_GENERICO, $RFC_EXTRANJERO) {

    test('retorna true para persona fisica (13 chars)', function () use ($RFC_FISICA) {
        expect(Rfc::of($RFC_FISICA)->isFisica())->toBeTrue();
    });

    test('retorna false para persona moral (12 chars)', function () use ($RFC_MORAL) {
        expect(Rfc::of($RFC_MORAL)->isFisica())->toBeFalse();
    });

    test('retorna false para RFC generico aunque tenga 13 chars', function () use ($RFC_GENERICO) {
        expect(Rfc::of($RFC_GENERICO)->isFisica())->toBeFalse();
    });

    test('retorna false para RFC de extranjero aunque tenga 13 chars', function () use ($RFC_EXTRANJERO) {
        expect(Rfc::of($RFC_EXTRANJERO)->isFisica())->toBeFalse();
    });
});

describe('isMoral()', function () use ($RFC_FISICA, $RFC_MORAL) {

    test('retorna true para persona moral (12 chars)', function () use ($RFC_MORAL) {
        expect(Rfc::of($RFC_MORAL)->isMoral())->toBeTrue();
    });

    test('retorna false para persona fisica (13 chars)', function () use ($RFC_FISICA) {
        expect(Rfc::of($RFC_FISICA)->isMoral())->toBeFalse();
    });
});

describe('isGeneric()', function () use ($RFC_FISICA, $RFC_GENERICO, $RFC_EXTRANJERO) {

    test('retorna true solo para XAXX010101000', function () use ($RFC_GENERICO) {
        expect(Rfc::of($RFC_GENERICO)->isGeneric())->toBeTrue();
    });

    test('retorna false para RFC normal de persona fisica', function () use ($RFC_FISICA) {
        expect(Rfc::of($RFC_FISICA)->isGeneric())->toBeFalse();
    });

    test('retorna false para RFC de extranjero', function () use ($RFC_EXTRANJERO) {
        expect(Rfc::of($RFC_EXTRANJERO)->isGeneric())->toBeFalse();
    });
});

describe('isForeign()', function () use ($RFC_FISICA, $RFC_GENERICO, $RFC_EXTRANJERO) {

    test('retorna true solo para XEXX010101000', function () use ($RFC_EXTRANJERO) {
        expect(Rfc::of($RFC_EXTRANJERO)->isForeign())->toBeTrue();
    });

    test('retorna false para RFC normal de persona fisica', function () use ($RFC_FISICA) {
        expect(Rfc::of($RFC_FISICA)->isForeign())->toBeFalse();
    });

    test('retorna false para RFC generico', function () use ($RFC_GENERICO) {
        expect(Rfc::of($RFC_GENERICO)->isForeign())->toBeFalse();
    });
});

describe('obtainDate()', function () use ($RFC_FISICA, $RFC_MORAL, $RFC_GENERICO, $RFC_EXTRANJERO) {

    test('extrae la fecha de un RFC de persona fisica', function () use ($RFC_FISICA) {
        $rfc = Rfc::of($RFC_FISICA);
        $date = $rfc->obtainDate();
        expect($date)->not->toBeNull();
        expect((int) $date->format('Y'))->toBe(1956);
        expect((int) $date->format('m'))->toBe(12);
        expect((int) $date->format('d'))->toBe(31);
    });

    test('extrae la fecha de un RFC de persona moral', function () use ($RFC_MORAL) {
        $rfc = Rfc::of($RFC_MORAL);
        $date = $rfc->obtainDate();
        expect($date)->not->toBeNull();
        expect((int) $date->format('Y'))->toBe(1986);
        expect((int) $date->format('m'))->toBe(8);
        expect((int) $date->format('d'))->toBe(29);
    });

    test('retorna null para RFC generico', function () use ($RFC_GENERICO) {
        expect(Rfc::of($RFC_GENERICO)->obtainDate())->toBeNull();
    });

    test('retorna null para RFC de extranjero', function () use ($RFC_EXTRANJERO) {
        expect(Rfc::of($RFC_EXTRANJERO)->obtainDate())->toBeNull();
    });
});

describe('equals()', function () use ($RFC_FISICA, $RFC_MORAL) {

    test('dos instancias con el mismo RFC son iguales', function () use ($RFC_FISICA) {
        $a = Rfc::of($RFC_FISICA);
        $b = Rfc::of($RFC_FISICA);
        expect($a->equals($b))->toBeTrue();
    });

    test('dos instancias con diferentes RFCs no son iguales', function () use ($RFC_FISICA, $RFC_MORAL) {
        $a = Rfc::of($RFC_FISICA);
        $b = Rfc::of($RFC_MORAL);
        expect($a->equals($b))->toBeFalse();
    });

    test('es reflexivo: a equals a es true', function () use ($RFC_FISICA) {
        $a = Rfc::of($RFC_FISICA);
        expect($a->equals($a))->toBeTrue();
    });

    test('es simetrico: a equals b == b equals a', function () use ($RFC_FISICA, $RFC_MORAL) {
        $a = Rfc::of($RFC_FISICA);
        $b = Rfc::of($RFC_MORAL);
        expect($a->equals($b))->toBe($b->equals($a));
    });
});

describe('toString()', function () use ($RFC_FISICA) {

    test('retorna el valor del RFC en mayusculas', function () use ($RFC_FISICA) {
        $rfc = Rfc::of($RFC_FISICA);
        expect($rfc->toString())->toBe($RFC_FISICA);
    });
});
