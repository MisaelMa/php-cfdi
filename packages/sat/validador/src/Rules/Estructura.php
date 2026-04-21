<?php

namespace Cfdi\Validador\Rules;

use Cfdi\Validador\CfdiData;
use Cfdi\Validador\ValidationIssue;

class Estructura
{
    private const VERSIONES_VALIDAS = ['3.3', '4.0'];
    private const TIPOS_COMPROBANTE = ['I', 'E', 'T', 'P', 'N'];
    private const CAMPOS_REQUERIDOS_COMUNES = [
        'Version', 'Fecha', 'LugarExpedicion', 'Moneda', 'SubTotal',
        'Total', 'TipoDeComprobante', 'NoCertificado', 'Sello', 'Certificado',
    ];
    private const CAMPOS_REQUERIDOS_40 = ['Exportacion'];

    /** @return ValidationIssue[] */
    public static function validate(CfdiData $data): array
    {
        return array_merge(
            self::checkVersion($data),
            self::checkCamposRequeridos($data),
            self::checkTipoDeComprobante($data),
            self::checkTipoTraslado($data),
            self::checkMonedaTipoCambio($data),
            self::checkFecha($data),
        );
    }

    private static function checkVersion(CfdiData $data): array
    {
        if (!in_array($data->version, self::VERSIONES_VALIDAS, true)) {
            return [new ValidationIssue('CFDI001', "Version '{$data->version}' no es valida. Se esperaba 3.3 o 4.0", 'estructura.version', 'Version')];
        }
        return [];
    }

    private static function checkCamposRequeridos(CfdiData $data): array
    {
        $campos = $data->version === '4.0'
            ? array_merge(self::CAMPOS_REQUERIDOS_COMUNES, self::CAMPOS_REQUERIDOS_40)
            : self::CAMPOS_REQUERIDOS_COMUNES;
        $issues = [];
        foreach ($campos as $campo) {
            if (!array_key_exists($campo, $data->comprobante)) {
                $issues[] = new ValidationIssue('CFDI002', "Campo requerido '{$campo}' no esta presente en el Comprobante", 'estructura.camposRequeridos', $campo);
            }
        }
        return $issues;
    }

    private static function checkTipoDeComprobante(CfdiData $data): array
    {
        $tipo = $data->comprobante['TipoDeComprobante'] ?? null;
        if ($tipo !== null && !in_array($tipo, self::TIPOS_COMPROBANTE, true)) {
            return [new ValidationIssue('CFDI003', "TipoDeComprobante '{$tipo}' no es valido", 'estructura.tipoDeComprobante', 'TipoDeComprobante')];
        }
        return [];
    }

    private static function checkTipoTraslado(CfdiData $data): array
    {
        $tipo = $data->comprobante['TipoDeComprobante'] ?? '';
        if ($tipo !== 'T') return [];
        $issues = [];
        $subtotal = $data->comprobante['SubTotal'] ?? '';
        $total = $data->comprobante['Total'] ?? '';
        if ($subtotal !== '0' && $subtotal !== '0.00') {
            $issues[] = new ValidationIssue('CFDI004', "Para TipoDeComprobante='T' (Traslado), SubTotal debe ser '0'", 'estructura.tipoTraslado', 'SubTotal');
        }
        if ($total !== '0' && $total !== '0.00') {
            $issues[] = new ValidationIssue('CFDI005', "Para TipoDeComprobante='T' (Traslado), Total debe ser '0'", 'estructura.tipoTraslado', 'Total');
        }
        return $issues;
    }

    private static function checkMonedaTipoCambio(CfdiData $data): array
    {
        $moneda = $data->comprobante['Moneda'] ?? null;
        $tipoCambio = $data->comprobante['TipoCambio'] ?? null;
        $issues = [];
        if ($moneda === 'XXX' && $tipoCambio !== null) {
            $issues[] = new ValidationIssue('CFDI006', "Cuando Moneda='XXX', no debe existir el atributo TipoCambio", 'estructura.monedaTipoCambio', 'TipoCambio');
        }
        if ($moneda !== null && $moneda !== 'MXN' && $moneda !== 'XXX' && $tipoCambio === null) {
            $issues[] = new ValidationIssue('CFDI007', "Cuando Moneda='{$moneda}' (distinta de MXN y XXX), TipoCambio es requerido", 'estructura.monedaTipoCambio', 'TipoCambio');
        }
        return $issues;
    }

    private static function checkFecha(CfdiData $data): array
    {
        $fecha = $data->comprobante['Fecha'] ?? null;
        if ($fecha !== null && !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $fecha)) {
            return [new ValidationIssue('CFDI008', "Fecha '{$fecha}' no tiene el formato ISO 8601 requerido", 'estructura.fecha', 'Fecha')];
        }
        return [];
    }
}
