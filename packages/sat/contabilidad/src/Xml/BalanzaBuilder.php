<?php

declare(strict_types=1);

namespace Sat\Contabilidad\Xml;

use Sat\Contabilidad\ContribuyenteInfo;
use Sat\Contabilidad\CuentaBalanza;
use Sat\Contabilidad\VersionContabilidad;

final class BalanzaBuilder
{
    private const NS_BCE_13 = 'http://www.sat.gob.mx/esquemas/ContabilidadE/1_3/BalanzaComprobacion';

    /**
     * @param list<CuentaBalanza> $cuentas
     */
    public static function build(
        ContribuyenteInfo $info,
        array $cuentas,
        VersionContabilidad $version = VersionContabilidad::V1_3,
    ): string {
        $ns = $version === VersionContabilidad::V1_3
            ? self::NS_BCE_13
            : str_replace('1_3', '1_1', self::NS_BCE_13);

        $lines = [];
        foreach ($cuentas as $c) {
            $lines[] = sprintf(
                '  <BCE:Ctas NumCta="%s" SaldoIni="%s" Debe="%s" Haber="%s" SaldoFin="%s"/>',
                $c->numCta,
                self::monto($c->saldoIni),
                self::monto($c->debe),
                self::monto($c->haber),
                self::monto($c->saldoFin),
            );
        }
        $cuentasXml = implode("\n", $lines);

        $ver = $version->value;

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<BCE:Balanza xmlns:BCE="{$ns}"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             Version="{$ver}"
             RFC="{$info->rfc}"
             Mes="{$info->mes}"
             Anio="{$info->anio}"
             TipoEnvio="{$info->tipoEnvio->value}">
{$cuentasXml}
</BCE:Balanza>
XML;
    }

    private static function monto(float $n): string
    {
        return number_format($n, 2, '.', '');
    }
}
