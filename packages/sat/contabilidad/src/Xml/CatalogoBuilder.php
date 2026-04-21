<?php

declare(strict_types=1);

namespace Sat\Contabilidad\Xml;

use Sat\Contabilidad\ContribuyenteInfo;
use Sat\Contabilidad\CuentaCatalogo;
use Sat\Contabilidad\VersionContabilidad;

final class CatalogoBuilder
{
    private const NS_CATALOGO_13 = 'http://www.sat.gob.mx/esquemas/ContabilidadE/1_3/CatalogoCuentas';

    /**
     * @param list<CuentaCatalogo> $cuentas
     */
    public static function build(
        ContribuyenteInfo $info,
        array $cuentas,
        VersionContabilidad $version = VersionContabilidad::V1_3,
    ): string {
        $ns = $version === VersionContabilidad::V1_3
            ? self::NS_CATALOGO_13
            : str_replace('1_3', '1_1', self::NS_CATALOGO_13);

        $lines = [];
        foreach ($cuentas as $c) {
            $subCta = $c->subCtaDe !== null && $c->subCtaDe !== ''
                ? sprintf(' SubCtaDe="%s"', $c->subCtaDe)
                : '';
            $lines[] = sprintf(
                '  <catalogocuentas:Ctas CodAgrup="%s" NumCta="%s" Desc="%s"%s Nivel="%d" Natur="%s"/>',
                $c->codAgrup,
                $c->numCta,
                $c->desc,
                $subCta,
                $c->nivel,
                $c->natur->value,
            );
        }
        $cuentasXml = implode("\n", $lines);

        $ver = $version->value;

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<catalogocuentas:Catalogo xmlns:catalogocuentas="{$ns}"
                          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                          Version="{$ver}"
                          RFC="{$info->rfc}"
                          Mes="{$info->mes}"
                          Anio="{$info->anio}"
                          TipoEnvio="{$info->tipoEnvio->value}">
{$cuentasXml}
</catalogocuentas:Catalogo>
XML;
    }
}
