<?php

namespace Cfdi\Validador\Rules;

use Cfdi\Validador\CfdiData;
use Cfdi\Validador\ValidationIssue;

class Impuestos
{
    private const TOLERANCIA = 0.011;
    private const IMPUESTOS_VALIDOS = ['001', '002', '003'];
    private const TIPOS_FACTOR_VALIDOS = ['Tasa', 'Cuota', 'Exento'];

    /** @return ValidationIssue[] */
    public static function validate(CfdiData $data): array
    {
        return array_merge(
            self::checkTrasladosConcepto($data),
            self::checkTrasladosGlobales($data),
            self::checkSumaTrasladados($data),
            self::checkSumaRetenidos($data),
        );
    }

    private static function parseDecimal(?string $val): ?float
    {
        if ($val === null || $val === '') return null;
        return is_numeric($val) ? (float) $val : null;
    }

    private static function checkImpuestoValido(?string $val, string $prefix): array
    {
        if ($val === null) return [];
        if (!in_array($val, self::IMPUESTOS_VALIDOS, true)) {
            return [new ValidationIssue('CFDI601', "Impuesto '{$val}' en {$prefix} no es valido", 'impuestos.impuestoValido', "{$prefix}.Impuesto")];
        }
        return [];
    }

    private static function checkTipoFactor(?string $val, string $prefix): array
    {
        if ($val === null) return [];
        if (!in_array($val, self::TIPOS_FACTOR_VALIDOS, true)) {
            return [new ValidationIssue('CFDI602', "TipoFactor '{$val}' en {$prefix} no es valido", 'impuestos.tipoFactor', "{$prefix}.TipoFactor")];
        }
        return [];
    }

    private static function checkExento(array $traslado, string $prefix): array
    {
        if (($traslado['TipoFactor'] ?? '') !== 'Exento') return [];
        $issues = [];
        if (array_key_exists('TasaOCuota', $traslado)) {
            $issues[] = new ValidationIssue('CFDI603', "TasaOCuota no debe estar presente cuando TipoFactor='Exento' en {$prefix}", 'impuestos.exento', "{$prefix}.TasaOCuota");
        }
        if (array_key_exists('Importe', $traslado)) {
            $issues[] = new ValidationIssue('CFDI604', "Importe no debe estar presente cuando TipoFactor='Exento' en {$prefix}", 'impuestos.exento', "{$prefix}.Importe");
        }
        return $issues;
    }

    private static function checkTrasladosConcepto(CfdiData $data): array
    {
        $issues = [];
        foreach ($data->conceptos as $ci => $concepto) {
            foreach ($concepto['impuestos']['traslados'] ?? [] as $ti => $t) {
                $prefix = "Concepto[{$ci}].Impuestos.Traslados[{$ti}]";
                $issues = array_merge($issues, self::checkImpuestoValido($t['Impuesto'] ?? null, $prefix));
                $issues = array_merge($issues, self::checkTipoFactor($t['TipoFactor'] ?? null, $prefix));
                $issues = array_merge($issues, self::checkExento($t, $prefix));
            }
            foreach ($concepto['impuestos']['retenciones'] ?? [] as $ri => $r) {
                $prefix = "Concepto[{$ci}].Impuestos.Retenciones[{$ri}]";
                $issues = array_merge($issues, self::checkImpuestoValido($r['Impuesto'] ?? null, $prefix));
            }
        }
        return $issues;
    }

    private static function checkTrasladosGlobales(CfdiData $data): array
    {
        if ($data->impuestos === null) return [];
        $issues = [];
        foreach ($data->impuestos['traslados'] as $i => $t) {
            $prefix = "Impuestos.Traslados[{$i}]";
            $issues = array_merge($issues, self::checkImpuestoValido($t['Impuesto'] ?? null, $prefix));
            $issues = array_merge($issues, self::checkTipoFactor($t['TipoFactor'] ?? null, $prefix));
            $issues = array_merge($issues, self::checkExento($t, $prefix));
        }
        foreach ($data->impuestos['retenciones'] as $i => $r) {
            $prefix = "Impuestos.Retenciones[{$i}]";
            $issues = array_merge($issues, self::checkImpuestoValido($r['Impuesto'] ?? null, $prefix));
        }
        return $issues;
    }

    private static function checkSumaTrasladados(CfdiData $data): array
    {
        $totalDeclarado = self::parseDecimal($data->impuestos['totalImpuestosTrasladados'] ?? null);
        if ($totalDeclarado === null) return [];
        $suma = 0.0;
        foreach ($data->conceptos as $concepto) {
            foreach ($concepto['impuestos']['traslados'] ?? [] as $t) {
                if (($t['TipoFactor'] ?? '') !== 'Exento') {
                    $imp = self::parseDecimal($t['Importe'] ?? null);
                    if ($imp !== null) $suma += $imp;
                }
            }
        }
        $diferencia = abs($totalDeclarado - $suma);
        if ($diferencia > self::TOLERANCIA) {
            return [new ValidationIssue('CFDI605', sprintf("TotalImpuestosTrasladados (%.2f) no coincide con suma (%.2f)", $totalDeclarado, $suma), 'impuestos.sumaTrasladados', 'Impuestos.TotalImpuestosTrasladados')];
        }
        return [];
    }

    private static function checkSumaRetenidos(CfdiData $data): array
    {
        $totalDeclarado = self::parseDecimal($data->impuestos['totalImpuestosRetenidos'] ?? null);
        if ($totalDeclarado === null) return [];
        $suma = 0.0;
        foreach ($data->conceptos as $concepto) {
            foreach ($concepto['impuestos']['retenciones'] ?? [] as $r) {
                $imp = self::parseDecimal($r['Importe'] ?? null);
                if ($imp !== null) $suma += $imp;
            }
        }
        $diferencia = abs($totalDeclarado - $suma);
        if ($diferencia > self::TOLERANCIA) {
            return [new ValidationIssue('CFDI606', sprintf("TotalImpuestosRetenidos (%.2f) no coincide con suma (%.2f)", $totalDeclarado, $suma), 'impuestos.sumaRetenidos', 'Impuestos.TotalImpuestosRetenidos')];
        }
        return [];
    }
}
