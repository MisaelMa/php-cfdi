<?php

namespace Cfdi\Validador\Rules;

use Cfdi\Validador\CfdiData;
use Cfdi\Validador\ValidationIssue;

class Conceptos
{
    private const TOLERANCIA = 0.011;

    /** @return ValidationIssue[] */
    public static function validate(CfdiData $data): array
    {
        $issues = [];
        if (empty($data->conceptos)) {
            $issues[] = new ValidationIssue('CFDI501', 'El CFDI debe tener al menos un Concepto', 'conceptos.minimo', 'Conceptos');
        }
        foreach ($data->conceptos as $idx => $concepto) {
            $issues = array_merge($issues, self::checkConcepto($concepto, $idx, $data->version));
        }
        return $issues;
    }

    private static function parseDecimal(?string $val): ?float
    {
        if ($val === null || $val === '') return null;
        return is_numeric($val) ? (float) $val : null;
    }

    private static function checkConcepto(array $concepto, int $idx, string $version): array
    {
        $issues = [];
        $attrs = $concepto['attributes'];
        $prefix = "Conceptos[{$idx}]";

        foreach (['ClaveProdServ', 'Cantidad', 'ClaveUnidad', 'Descripcion', 'ValorUnitario', 'Importe'] as $campo) {
            if (!array_key_exists($campo, $attrs)) {
                $issues[] = new ValidationIssue('CFDI502', "Campo requerido '{$campo}' no presente en Concepto[{$idx}]", 'conceptos.camposRequeridos', "{$prefix}.{$campo}");
            }
        }

        $cantidad = self::parseDecimal($attrs['Cantidad'] ?? null);
        if ($cantidad !== null && $cantidad <= 0) {
            $issues[] = new ValidationIssue('CFDI503', "Cantidad en Concepto[{$idx}] debe ser mayor a 0", 'conceptos.cantidad', "{$prefix}.Cantidad");
        }

        $valorUnitario = self::parseDecimal($attrs['ValorUnitario'] ?? null);
        if ($valorUnitario !== null && $valorUnitario < 0) {
            $issues[] = new ValidationIssue('CFDI504', "ValorUnitario en Concepto[{$idx}] no puede ser negativo", 'conceptos.valorUnitario', "{$prefix}.ValorUnitario");
        }

        $importe = self::parseDecimal($attrs['Importe'] ?? null);
        if ($importe !== null && $importe < 0) {
            $issues[] = new ValidationIssue('CFDI505', "Importe en Concepto[{$idx}] no puede ser negativo", 'conceptos.importe', "{$prefix}.Importe");
        }

        if ($cantidad !== null && $valorUnitario !== null && $importe !== null) {
            $importeEsperado = $cantidad * $valorUnitario;
            $diferencia = abs($importe - $importeEsperado);
            if ($diferencia > self::TOLERANCIA) {
                $issues[] = new ValidationIssue('CFDI506', "Importe en Concepto[{$idx}] no coincide con Cantidad * ValorUnitario", 'conceptos.importeCalculado', "{$prefix}.Importe");
            }
        }

        $descuento = self::parseDecimal($attrs['Descuento'] ?? null);
        if ($descuento !== null && $importe !== null && $descuento > $importe + self::TOLERANCIA) {
            $issues[] = new ValidationIssue('CFDI507', "Descuento en Concepto[{$idx}] no puede ser mayor al Importe", 'conceptos.descuento', "{$prefix}.Descuento");
        }

        if ($version === '4.0' && empty(trim($attrs['ObjetoImp'] ?? ''))) {
            $issues[] = new ValidationIssue('CFDI508', "ObjetoImp es requerido en Concepto[{$idx}] para CFDI 4.0", 'conceptos.objetoImp', "{$prefix}.ObjetoImp");
        }

        return $issues;
    }
}
