<?php

declare(strict_types=1);

use Sat\Contabilidad\Contabilidad;
use Sat\Contabilidad\ContribuyenteInfo;
use Sat\Contabilidad\CuentaAuxiliar;
use Sat\Contabilidad\CuentaBalanza;
use Sat\Contabilidad\CuentaCatalogo;
use Sat\Contabilidad\NaturalezaCuenta;
use Sat\Contabilidad\Poliza;
use Sat\Contabilidad\PolizaDetalle;
use Sat\Contabilidad\TipoEnvio;
use Sat\Contabilidad\TipoSolicitud;
use Sat\Contabilidad\TransaccionAuxiliar;
use Sat\Contabilidad\VersionContabilidad;
use Sat\Contabilidad\Xml\AuxiliarBuilder;
use Sat\Contabilidad\Xml\BalanzaBuilder;
use Sat\Contabilidad\Xml\CatalogoBuilder;
use Sat\Contabilidad\Xml\PolizasBuilder;

describe('BalanzaBuilder', function () {
    test('coincide con salida esperada (1.3, una cuenta)', function () {
        $info = new ContribuyenteInfo('XAXX010101000', '01', 2024, TipoEnvio::Normal);
        $cuentas = [
            new CuentaBalanza('100-01-001', 100.5, 10, 0, 110.5),
        ];

        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<BCE:Balanza xmlns:BCE="http://www.sat.gob.mx/esquemas/ContabilidadE/1_3/BalanzaComprobacion"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             Version="1.3"
             RFC="XAXX010101000"
             Mes="01"
             Anio="2024"
             TipoEnvio="N">
  <BCE:Ctas NumCta="100-01-001" SaldoIni="100.50" Debe="10.00" Haber="0.00" SaldoFin="110.50"/>
</BCE:Balanza>
XML;

        expect(BalanzaBuilder::build($info, $cuentas))->toBe($expected);
    });

    test('usa namespace 1.1 cuando la versión es 1.1', function () {
        $info = new ContribuyenteInfo('AAA010101AAA', '12', 2023, TipoEnvio::Complementaria);

        $xml = BalanzaBuilder::build($info, [], VersionContabilidad::V1_1);

        expect($xml)->toContain('http://www.sat.gob.mx/esquemas/ContabilidadE/1_1/BalanzaComprobacion');
        expect($xml)->toContain('Version="1.1"');
        expect($xml)->toContain('TipoEnvio="C"');
    });
});

describe('CatalogoBuilder', function () {
    test('omite SubCtaDe cuando no viene definido', function () {
        $info = new ContribuyenteInfo('EKU9003173C9', '06', 2025, TipoEnvio::Normal);
        $sinSub = new CuentaCatalogo('100', '100-001', 'Caja', 1, NaturalezaCuenta::Deudora);

        $xml = CatalogoBuilder::build($info, [$sinSub]);

        expect($xml)->toContain('Desc="Caja" Nivel="1" Natur="D"');
        expect($xml)->not->toContain('SubCtaDe');
    });

    test('incluye SubCtaDe cuando viene definido', function () {
        $info = new ContribuyenteInfo('EKU9003173C9', '06', 2025, TipoEnvio::Normal);
        $conSub = new CuentaCatalogo('101', '100-002', 'Banco', 2, NaturalezaCuenta::Deudora, '100-001');

        $xml = CatalogoBuilder::build($info, [$conSub]);

        expect($xml)->toContain('Desc="Banco" SubCtaDe="100-001" Nivel="2"');
    });
});

describe('PolizasBuilder', function () {
    test('estructura y NumUnIdenPol coinciden con el esquema Node', function () {
        $info = new ContribuyenteInfo('XAXX010101000', '03', 2024, TipoEnvio::Normal);
        $polizas = [
            new Poliza(
                'P-1',
                '2024-03-15',
                'Diario',
                [
                    new PolizaDetalle('1', 'Abono', 0, 100.0, '201-001'),
                    new PolizaDetalle('2', 'Cargo', 100.0, 0, '101-001'),
                ],
            ),
        ];

        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<PLZ:Polizas xmlns:PLZ="http://www.sat.gob.mx/esquemas/ContabilidadE/1_3/PolizasPeriodo"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             Version="1.3"
             RFC="XAXX010101000"
             Mes="03"
             Anio="2024"
             TipoEnvio="N"
             TipoSolicitud="AF">
    <PLZ:Poliza NumUnIdenPol="P-1" Fecha="2024-03-15" Concepto="Diario">
      <PLZ:Transaccion NumCta="201-001" Concepto="Abono" Debe="0.00" Haber="100.00"/>
      <PLZ:Transaccion NumCta="101-001" Concepto="Cargo" Debe="100.00" Haber="0.00"/>
    </PLZ:Poliza>
</PLZ:Polizas>
XML;

        expect(PolizasBuilder::build($info, $polizas, TipoSolicitud::AF))->toBe($expected);
    });
});

describe('AuxiliarBuilder', function () {
    test('genera Cuenta y DetalleAux con montos a dos decimales', function () {
        $info = new ContribuyenteInfo('XAXX010101000', '01', 2024, TipoEnvio::Normal);
        $cuentas = [
            new CuentaAuxiliar(
                '101-001',
                'Bancos',
                500,
                400,
                [
                    new TransaccionAuxiliar('2024-01-10', 'A-1', 'Retiro', 0, 50.25),
                    new TransaccionAuxiliar('2024-01-11', 'A-2', 'Depósito', 25.5, 0),
                ],
            ),
        ];

        $expected = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<AuxiliarCtas:AuxiliarCtas xmlns:AuxiliarCtas="http://www.sat.gob.mx/esquemas/ContabilidadE/1_3/AuxiliarCtas"
                           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                           Version="1.3"
                           RFC="XAXX010101000"
                           Mes="01"
                           Anio="2024"
                           TipoEnvio="N"
                           TipoSolicitud="CO">
    <AuxiliarCtas:Cuenta NumCta="101-001" DesCta="Bancos" SaldoIni="500.00" SaldoFin="400.00">
      <AuxiliarCtas:DetalleAux Fecha="2024-01-10" NumUnIdenPol="A-1" Concepto="Retiro" Debe="0.00" Haber="50.25"/>
      <AuxiliarCtas:DetalleAux Fecha="2024-01-11" NumUnIdenPol="A-2" Concepto="Depósito" Debe="25.50" Haber="0.00"/>
    </AuxiliarCtas:Cuenta>
</AuxiliarCtas:AuxiliarCtas>
XML;

        expect(AuxiliarBuilder::build($info, $cuentas, TipoSolicitud::CO))->toBe($expected);
    });
});

describe('Contabilidad', function () {
    test('delega a BalanzaBuilder', function () {
        $info = new ContribuyenteInfo('XAXX010101000', '01', 2024, TipoEnvio::Normal);

        expect(Contabilidad::buildBalanzaXml($info, []))->toBe(BalanzaBuilder::build($info, []));
    });
});
