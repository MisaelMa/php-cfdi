<?php

declare(strict_types=1);

use Sat\Diot\DiotDeclaracion;
use Sat\Diot\DiotTxtBuilder;
use Sat\Diot\OperacionTercero;
use Sat\Diot\TipoOperacion;
use Sat\Diot\TipoTercero;

describe('TipoTercero', function () {
    test('valores coinciden con catálogo DIOT', function () {
        expect(TipoTercero::ProveedorNacional->value)->toBe('04');
        expect(TipoTercero::ProveedorExtranjero->value)->toBe('05');
        expect(TipoTercero::ProveedorGlobal->value)->toBe('15');
    });
});

describe('TipoOperacion', function () {
    test('valores coinciden con catálogo DIOT', function () {
        expect(TipoOperacion::ProfesionalesHonorarios->value)->toBe('85');
        expect(TipoOperacion::Arrendamiento->value)->toBe('06');
        expect(TipoOperacion::OtrosConIVA->value)->toBe('03');
        expect(TipoOperacion::OtrosSinIVA->value)->toBe('04');
    });
});

describe('DiotTxtBuilder', function () {
    test('sin operaciones devuelve cadena vacía', function () {
        $decl = new DiotDeclaracion('EKU9003173C9', 2024, 1, []);

        expect(DiotTxtBuilder::build($decl))->toBe('');
    });

    test('genera una línea por operación con montos a dos decimales', function () {
        $op = new OperacionTercero(
            tipoTercero: TipoTercero::ProveedorNacional,
            tipoOperacion: TipoOperacion::OtrosConIVA,
            montoIva16: 1600.5,
            montoIva0: 0,
            montoExento: 0,
            montoRetenido: 0,
            montoIvaNoDeduc: 0,
            rfc: 'AAA010101AAA',
        );

        $decl = new DiotDeclaracion('EKU9003173C9', 2024, 1, [$op]);

        $expected = '04|03|AAA010101AAA|||||1600.50|0.00|0.00|0.00|0.00';

        expect(DiotTxtBuilder::build($decl))->toBe($expected);
    });

    test('varias operaciones van separadas por salto de línea', function () {
        $op1 = new OperacionTercero(
            TipoTercero::ProveedorNacional,
            TipoOperacion::OtrosConIVA,
            100,
            0,
            0,
            0,
            0,
            'RFC1',
        );
        $op2 = new OperacionTercero(
            TipoTercero::ProveedorExtranjero,
            TipoOperacion::Arrendamiento,
            0,
            50,
            0,
            0,
            0,
            null,
            'EXT-123',
            'ACME Corp',
            'USA',
            'US',
        );

        $decl = new DiotDeclaracion('XAXX010101000', 2023, 12, [$op1, $op2]);

        $lines = explode("\n", DiotTxtBuilder::build($decl));

        expect($lines)->toHaveCount(2);
        expect($lines[0])->toBe('04|03|RFC1|||||100.00|0.00|0.00|0.00|0.00');
        expect($lines[1])->toBe('05|06||EXT-123|ACME Corp|USA|US|0.00|50.00|0.00|0.00|0.00');
    });

    test('rechaza montos con más de dos decimales', function () {
        $op = new OperacionTercero(
            TipoTercero::ProveedorNacional,
            TipoOperacion::OtrosConIVA,
            10.001,
            0,
            0,
            0,
            0,
        );

        $decl = new DiotDeclaracion('EKU9003173C9', 2024, 1, [$op]);

        DiotTxtBuilder::build($decl);
    })->throws(\InvalidArgumentException::class);
});
