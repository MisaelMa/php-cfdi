<?php

declare(strict_types=1);

namespace Sat\Contabilidad;

use Sat\Contabilidad\Xml\AuxiliarBuilder;
use Sat\Contabilidad\Xml\BalanzaBuilder;
use Sat\Contabilidad\Xml\CatalogoBuilder;
use Sat\Contabilidad\Xml\PolizasBuilder;

/**
 * Punto de entrada para generar XML de Contabilidad Electrónica (Anexo 24 RMF SAT).
 *
 * @see https://www.sat.gob.mx/consultas/42150/contabilidad-electronica
 */
final class Contabilidad
{
    private function __construct()
    {
    }

    /**
     * @param list<CuentaBalanza> $cuentas
     */
    public static function buildBalanzaXml(
        ContribuyenteInfo $info,
        array $cuentas,
        VersionContabilidad $version = VersionContabilidad::V1_3,
    ): string {
        return BalanzaBuilder::build($info, $cuentas, $version);
    }

    /**
     * @param list<CuentaCatalogo> $cuentas
     */
    public static function buildCatalogoXml(
        ContribuyenteInfo $info,
        array $cuentas,
        VersionContabilidad $version = VersionContabilidad::V1_3,
    ): string {
        return CatalogoBuilder::build($info, $cuentas, $version);
    }

    /**
     * @param list<Poliza> $polizas
     */
    public static function buildPolizasXml(
        ContribuyenteInfo $info,
        array $polizas,
        TipoSolicitud $tipoSolicitud,
        VersionContabilidad $version = VersionContabilidad::V1_3,
    ): string {
        return PolizasBuilder::build($info, $polizas, $tipoSolicitud, $version);
    }

    /**
     * @param list<CuentaAuxiliar> $cuentas
     */
    public static function buildAuxiliarXml(
        ContribuyenteInfo $info,
        array $cuentas,
        TipoSolicitud $tipoSolicitud,
        VersionContabilidad $version = VersionContabilidad::V1_3,
    ): string {
        return AuxiliarBuilder::build($info, $cuentas, $tipoSolicitud, $version);
    }
}
