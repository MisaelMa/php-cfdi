<?php

declare(strict_types=1);

namespace Sat\Contabilidad\Xml;

use Sat\Contabilidad\ContribuyenteInfo;
use Sat\Contabilidad\CuentaAuxiliar;
use Sat\Contabilidad\TipoSolicitud;
use Sat\Contabilidad\VersionContabilidad;

final class AuxiliarBuilder
{
    private const NS_AUX_13 = 'http://www.sat.gob.mx/esquemas/ContabilidadE/1_3/AuxiliarCtas';

    /**
     * @param list<CuentaAuxiliar> $cuentas
     */
    public static function build(
        ContribuyenteInfo $info,
        array $cuentas,
        TipoSolicitud $tipoSolicitud,
        VersionContabilidad $version = VersionContabilidad::V1_3,
    ): string {
        $ns = $version === VersionContabilidad::V1_3
            ? self::NS_AUX_13
            : str_replace('1_3', '1_1', self::NS_AUX_13);

        $bloques = [];
        foreach ($cuentas as $c) {
            $txLines = [];
            foreach ($c->transacciones as $t) {
                $txLines[] = sprintf(
                    '      <AuxiliarCtas:DetalleAux Fecha="%s" NumUnIdenPol="%s" Concepto="%s" Debe="%s" Haber="%s"/>',
                    $t->fecha,
                    $t->numPoliza,
                    $t->concepto,
                    self::monto($t->debe),
                    self::monto($t->haber),
                );
            }
            $txXml = implode("\n", $txLines);
            $saldoIni = self::monto($c->saldoIni);
            $saldoFin = self::monto($c->saldoFin);
            $bloques[] = <<<XML
    <AuxiliarCtas:Cuenta NumCta="{$c->numCta}" DesCta="{$c->desCta}" SaldoIni="{$saldoIni}" SaldoFin="{$saldoFin}">
{$txXml}
    </AuxiliarCtas:Cuenta>
XML;
        }
        $cuentasXml = implode("\n", $bloques);

        $ver = $version->value;
        $tipoSol = $tipoSolicitud->value;

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<AuxiliarCtas:AuxiliarCtas xmlns:AuxiliarCtas="{$ns}"
                           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                           Version="{$ver}"
                           RFC="{$info->rfc}"
                           Mes="{$info->mes}"
                           Anio="{$info->anio}"
                           TipoEnvio="{$info->tipoEnvio->value}"
                           TipoSolicitud="{$tipoSol}">
{$cuentasXml}
</AuxiliarCtas:AuxiliarCtas>
XML;
    }

    private static function monto(float $n): string
    {
        return number_format($n, 2, '.', '');
    }
}
