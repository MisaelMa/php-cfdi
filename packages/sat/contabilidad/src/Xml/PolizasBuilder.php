<?php

declare(strict_types=1);

namespace Sat\Contabilidad\Xml;

use Sat\Contabilidad\ContribuyenteInfo;
use Sat\Contabilidad\Poliza;
use Sat\Contabilidad\TipoSolicitud;
use Sat\Contabilidad\VersionContabilidad;

final class PolizasBuilder
{
    private const NS_PLZ_13 = 'http://www.sat.gob.mx/esquemas/ContabilidadE/1_3/PolizasPeriodo';

    /**
     * @param list<Poliza> $polizas
     */
    public static function build(
        ContribuyenteInfo $info,
        array $polizas,
        TipoSolicitud $tipoSolicitud,
        VersionContabilidad $version = VersionContabilidad::V1_3,
    ): string {
        $ns = $version === VersionContabilidad::V1_3
            ? self::NS_PLZ_13
            : str_replace('1_3', '1_1', self::NS_PLZ_13);

        $bloques = [];
        foreach ($polizas as $p) {
            $detalleLines = [];
            foreach ($p->detalle as $d) {
                $detalleLines[] = sprintf(
                    '      <PLZ:Transaccion NumCta="%s" Concepto="%s" Debe="%s" Haber="%s"/>',
                    $d->numCta,
                    $d->concepto,
                    self::monto($d->debe),
                    self::monto($d->haber),
                );
            }
            $detalleXml = implode("\n", $detalleLines);
            $bloques[] = <<<XML
    <PLZ:Poliza NumUnIdenPol="{$p->numPoliza}" Fecha="{$p->fecha}" Concepto="{$p->concepto}">
{$detalleXml}
    </PLZ:Poliza>
XML;
        }
        $polizasXml = implode("\n", $bloques);

        $ver = $version->value;
        $tipoSol = $tipoSolicitud->value;

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<PLZ:Polizas xmlns:PLZ="{$ns}"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             Version="{$ver}"
             RFC="{$info->rfc}"
             Mes="{$info->mes}"
             Anio="{$info->anio}"
             TipoEnvio="{$info->tipoEnvio->value}"
             TipoSolicitud="{$tipoSol}">
{$polizasXml}
</PLZ:Polizas>
XML;
    }

    private static function monto(float $n): string
    {
        return number_format($n, 2, '.', '');
    }
}
