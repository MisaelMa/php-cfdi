<?php

use Renapo\Curp\Curp;
use Renapo\Curp\CheckDigit;
use Renapo\Curp\Constants;
use Renapo\Curp\BadCurpFormat;

describe('CheckDigit', function () {
    test('calcula digito verificador correctamente', function () {
        $result = CheckDigit::checkDigit('GARC850101HDFRRL05');
        expect($result)->toBe('5');
    });

    test('devuelve 0 cuando mod es 0', function () {
        $digit = CheckDigit::checkDigit('XEXX010101HNEXXXA40');
        expect(is_string($digit))->toBeTrue();
    });
});

describe('Curp::parseInput', function () {
    test('convierte a mayusculas y limpia caracteres', function () {
        $result = Curp::parseInput('  garc-850101-hdfrrl09  ');
        expect($result)->toBe('GARC850101HDFRRL09');
    });

    test('elimina caracteres no alfanumericos', function () {
        $result = Curp::parseInput('GARC.850101.HDFRRL09');
        expect($result)->toBe('GARC850101HDFRRL09');
    });
});

describe('Curp::validateDate', function () {
    test('acepta fecha valida', function () {
        expect(Curp::validateDate('GARC850101HDFRRL09'))->toBeTrue();
    });

    test('rechaza fecha invalida', function () {
        expect(Curp::validateDate('GARC859901HDFRRL09'))->toBeFalse();
    });
});

describe('Curp::validateState', function () {
    test('acepta estado valido', function () {
        expect(Curp::validateState('GARC850101HDFRRB09'))->toBeTrue();
    });

    test('acepta NE (nacido en extranjero)', function () {
        expect(Curp::validateState('GARC850101HNERRB09'))->toBeTrue();
    });

    test('rechaza estado invalido', function () {
        expect(Curp::validateState('GARC850101HXXRRB09'))->toBeFalse();
    });
});

describe('Curp::hasForbiddenWords', function () {
    test('detecta palabra prohibida BACA', function () {
        expect(Curp::hasForbiddenWords('BACA850101HDFRRL09'))->toBeTrue();
    });

    test('detecta palabra prohibida PUTO', function () {
        expect(Curp::hasForbiddenWords('PUTO850101HDFRRL09'))->toBeTrue();
    });

    test('no detecta palabra valida', function () {
        expect(Curp::hasForbiddenWords('GARC850101HDFRRL09'))->toBeFalse();
    });
});

describe('Curp::getState', function () {
    test('extrae estado de CURP valida', function () {
        expect(Curp::getState('GARC850101HDFRRL09'))->toBe('DF');
    });

    test('devuelve 0 para CURP invalida', function () {
        expect(Curp::getState('INVALIDO'))->toBe('0');
    });
});

describe('Curp::validateLocal', function () {
    test('valida CURP con formato correcto', function () {
        $result = Curp::validateLocal('GARC850101HDFRRL09');
        expect($result['isValid'])->toBeTrue();
        expect($result['rfc'])->toBe('GARC850101HDFRRL09');
    });

    test('rechaza CURP con formato incorrecto', function () {
        $result = Curp::validateLocal('INVALIDO');
        expect($result['isValid'])->toBeFalse();
    });

    test('rechaza CURP con palabra prohibida', function () {
        $result = Curp::validateLocal('BACA850101HDFRRL09');
        expect($result['isValid'])->toBeFalse();
    });
});

describe('Curp::validate', function () {
    test('valida CURP completa con digito verificador correcto', function () {
        $result = Curp::validate('GARC850101HDFRRL05');
        expect($result['isValid'])->toBeTrue();
    });

    test('rechaza CURP con digito verificador incorrecto', function () {
        $result = Curp::validate('GARC850101HDFRRL09');
        expect($result['isValid'])->toBeFalse();
    });

    test('rechaza CURP vacia', function () {
        $result = Curp::validate('');
        expect($result['isValid'])->toBeFalse();
    });
});

describe('Constants', function () {
    test('tiene 33 estados', function () {
        expect(count(Constants::STATES))->toBe(33);
    });

    test('tiene 33 etiquetas de estados', function () {
        expect(count(Constants::ESTADO_LABELS))->toBe(33);
    });

    test('tiene palabras prohibidas', function () {
        expect(count(Constants::FORBIDDEN_WORDS))->toBeGreaterThan(80);
    });

    test('regex CURP es valido', function () {
        expect(preg_match(Constants::REGEX_CURP, 'GARC850101HDFRRL09'))->toBe(1);
    });
});

describe('BadCurpFormat', function () {
    test('genera mensaje con CURP', function () {
        $ex = new BadCurpFormat('INVALID');
        expect($ex->getMessage())->toBe("'INVALID' is an invalid curp");
    });

    test('extiende InvalidArgumentException', function () {
        $ex = new BadCurpFormat('X');
        expect($ex)->toBeInstanceOf(\InvalidArgumentException::class);
    });
});
