<?php

namespace Cfdi\Validador\Rules;

use Cfdi\Validador\CfdiData;
use Cfdi\Validador\ValidationIssue;

class Montos
{
    private const TOLERANCIA = 0.01;

    /** @return ValidationIssue[] */
    public static function validate(CfdiData $data): array
    {
        return array_merge(
            self::checkSubTotal($data),
            self::checkTotal($data),
            self::checkDescuento($data),
            self::checkTotalCalculado($data),
        );
    }

    private static function parseDecimal(?string $val): ?float
    {
        if ($val === null || $val === '') return null;
        return is_numeric($val) ? (float) $val : null;
    }

    private static function checkSubTotal(CfdiData $data): array
    {
        $val = $data->comprobante['SubTotal'] ?? null;
        $n = self::parseDecimal($val);
        $issues = [];
        if ($val !== null && $n === null) {
            $issues[] = new ValidationIssue('CFDI202', "SubTotal '{$val}' no es un numero valido", 'montos.subtotal', 'SubTotal');
            return $issues;
        }
        if ($n !== null && $n < 0) {
            $issues[] = new ValidationIssue('CFDI203', "SubTotal no puede ser negativo: '{$val}'", 'montos.subtotal', 'SubTotal');
        }
        return $issues;
    }

    private static function checkTotal(CfdiData $data): array
    {
        $val = $data->comprobante['Total'] ?? null;
        $n = self::parseDecimal($val);
        $issues = [];
        if ($val !== null && $n === null) {
            $issues[] = new ValidationIssue('CFDI204', "Total '{$val}' no es un numero valido", 'montos.total', 'Total');
            return $issues;
        }
        if ($n !== null && $n < 0) {
            $issues[] = new ValidationIssue('CFDI205', "Total no puede ser negativo: '{$val}'", 'montos.total', 'Total');
        }
        return $issues;
    }

    private static function checkDescuento(CfdiData $data): array
    {
        $descuentoVal = $data->comprobante['Descuento'] ?? null;
        if ($descuentoVal === null) return [];
        $descuento = self::parseDecimal($descuentoVal);
        $subtotal = self::parseDecimal($data->comprobante['SubTotal'] ?? null);
        $issues = [];
        if ($descuento === null) {
            $issues[] = new ValidationIssue('CFDI206', "Descuento '{$descuentoVal}' no es un numero valido", 'montos.descuento', 'Descuento');
            return $issues;
        }
        if ($subtotal !== null && $descuento > $subtotal + self::TOLERANCIA) {
            $issues[] = new ValidationIssue('CFDI207', "Descuento ({$descuento}) no puede ser mayor que SubTotal ({$subtotal})", 'montos.descuento', 'Descuento');
        }
        return $issues;
    }

    private static function checkTotalCalculado(CfdiData $data): array
    {
        $subtotal = self::parseDecimal($data->comprobante['SubTotal'] ?? null);
        $total = self::parseDecimal($data->comprobante['Total'] ?? null);
        $descuento = self::parseDecimal($data->comprobante['Descuento'] ?? null) ?? 0;
        $trasladados = self::parseDecimal($data->impuestos['totalImpuestosTrasladados'] ?? null) ?? 0;
        $retenidos = self::parseDecimal($data->impuestos['totalImpuestosRetenidos'] ?? null) ?? 0;

        if ($subtotal === null || $total === null) return [];

        $totalEsperado = $subtotal - $descuento + $trasladados - $retenidos;
        $diferencia = abs($total - $totalEsperado);

        if ($diferencia > self::TOLERANCIA) {
            return [new ValidationIssue('CFDI208', sprintf("Total (%s) no coincide con calculo esperado = %.2f (diferencia: %.6f)", $total, $totalEsperado, $diferencia), 'montos.totalCalculado', 'Total')];
        }
        return [];
    }
}
