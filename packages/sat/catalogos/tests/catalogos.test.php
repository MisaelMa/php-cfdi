<?php

use Cfdi\Catalogos\FormaPago;
use Cfdi\Catalogos\Exportacion;
use Cfdi\Catalogos\MetodoPago;
use Cfdi\Catalogos\TipoComprobante;
use Cfdi\Catalogos\Impuesto;
use Cfdi\Catalogos\UsoCFDI;
use Cfdi\Catalogos\RegimenFiscal;
use Cfdi\Catalogos\NivelEducativo;

describe('FormaPago', function () {
    test('EFECTIVO tiene valor 01', function () {
        expect(FormaPago::EFECTIVO->value)->toBe('01');
    });

    test('TRANSFERENCIA_ELECTRONICA tiene valor 03', function () {
        expect(FormaPago::TRANSFERENCIA_ELECTRONICA->value)->toBe('03');
    });

    test('POR_DEFINIR tiene valor 99', function () {
        expect(FormaPago::POR_DEFINIR->value)->toBe('99');
    });

    test('label retorna descripcion correcta', function () {
        expect(FormaPago::EFECTIVO->label())->toBe('Efectivo');
        expect(FormaPago::TARJETA_DE_CREDITO->label())->toBe('Tarjeta de crédito');
    });

    test('se puede crear desde valor string', function () {
        expect(FormaPago::from('03'))->toBe(FormaPago::TRANSFERENCIA_ELECTRONICA);
    });

    test('tryFrom retorna null para valor invalido', function () {
        expect(FormaPago::tryFrom('XX'))->toBeNull();
    });
});

describe('Exportacion', function () {
    test('NO_APLICA tiene valor 01', function () {
        expect(Exportacion::NO_APLICA->value)->toBe('01');
    });

    test('tiene 3 casos', function () {
        expect(count(Exportacion::cases()))->toBe(3);
    });
});

describe('MetodoPago', function () {
    test('PUE tiene valor correcto', function () {
        expect(MetodoPago::PAGO_EN_UNA_EXHIBICION->value)->toBe('PUE');
    });

    test('PPD tiene valor correcto', function () {
        expect(MetodoPago::PAGO_EN_PARCIALIDADES_DIFERIDO->value)->toBe('PPD');
    });

    test('label retorna descripcion correcta', function () {
        expect(MetodoPago::PAGO_EN_UNA_EXHIBICION->label())->toBe('Pago en una sola exhibición');
    });

    test('tiene 2 casos', function () {
        expect(count(MetodoPago::cases()))->toBe(2);
    });
});

describe('TipoComprobante', function () {
    test('INGRESO tiene valor I', function () {
        expect(TipoComprobante::INGRESO->value)->toBe('I');
    });

    test('PAGO tiene valor P', function () {
        expect(TipoComprobante::PAGO->value)->toBe('P');
    });

    test('NOMINA tiene valor N', function () {
        expect(TipoComprobante::NOMINA->value)->toBe('N');
    });

    test('tiene 5 tipos', function () {
        expect(count(TipoComprobante::cases()))->toBe(5);
    });

    test('label retorna descripcion correcta', function () {
        expect(TipoComprobante::INGRESO->label())->toBe('Ingreso');
    });
});

describe('Impuesto', function () {
    test('ISR tiene valor 001', function () {
        expect(Impuesto::ISR->value)->toBe('001');
    });

    test('IVA tiene valor 002', function () {
        expect(Impuesto::IVA->value)->toBe('002');
    });

    test('IEPS tiene valor 003', function () {
        expect(Impuesto::IEPS->value)->toBe('003');
    });

    test('tiene 3 impuestos', function () {
        expect(count(Impuesto::cases()))->toBe(3);
    });
});

describe('UsoCFDI', function () {
    test('GASTOS_EN_GENERAL tiene valor G03', function () {
        expect(UsoCFDI::GASTOS_EN_GENERAL->value)->toBe('G03');
    });

    test('POR_DEFINIR tiene valor P01', function () {
        expect(UsoCFDI::POR_DEFINIR->value)->toBe('P01');
    });

    test('SIN_EFECTOS_FISCALES tiene valor S01', function () {
        expect(UsoCFDI::SIN_EFECTOS_FISCALES->value)->toBe('S01');
    });

    test('PAGOS tiene valor CP01', function () {
        expect(UsoCFDI::PAGOS->value)->toBe('CP01');
    });

    test('label retorna descripcion correcta', function () {
        expect(UsoCFDI::GASTOS_EN_GENERAL->label())->toBe('Gastos en general');
    });
});

describe('RegimenFiscal', function () {
    test('contiene al menos 20 regimenes', function () {
        expect(count(RegimenFiscal::LIST))->toBeGreaterThanOrEqual(20);
    });

    test('find retorna el regimen correcto', function () {
        $regimen = RegimenFiscal::find(601);
        expect($regimen)->not->toBeNull();
        expect($regimen['descripcion'])->toBe('General de Ley Personas Morales');
    });

    test('find retorna null para valor inexistente', function () {
        expect(RegimenFiscal::find(999))->toBeNull();
    });

    test('forFisica retorna solo regimenes de persona fisica', function () {
        $lista = RegimenFiscal::forFisica();
        foreach ($lista as $item) {
            expect($item['fisica'])->toBeTrue();
        }
    });

    test('forMoral retorna solo regimenes de persona moral', function () {
        $lista = RegimenFiscal::forMoral();
        foreach ($lista as $item) {
            expect($item['moral'])->toBeTrue();
        }
    });
});

describe('NivelEducativo', function () {
    test('PREESCOLAR tiene valor correcto', function () {
        expect(NivelEducativo::PREESCOLAR->value)->toBe('Preescolar');
    });

    test('BACHILLERATO tiene valor correcto', function () {
        expect(NivelEducativo::BACHILLERATO->value)->toBe('Bachillerato o su equivalente');
    });

    test('tiene 5 niveles', function () {
        expect(count(NivelEducativo::cases()))->toBe(5);
    });
});
