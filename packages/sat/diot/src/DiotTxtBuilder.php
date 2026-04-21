<?php

declare(strict_types=1);

namespace Sat\Diot;

/**
 * Genera el contenido de archivo DIOT en formato texto delimitado por tubería (|),
 * una línea por operación con terceros.
 *
 * Formato por línea:
 * `TipoTercero|TipoOperacion|RFC|IDFiscal|Nombre|Pais|Nacionalidad|IVA16|IVA0|Exento|Retenido|NoDeduc`
 */
final class DiotTxtBuilder
{
    public static function build(DiotDeclaracion $declaracion): string
    {
        if ($declaracion->operaciones === []) {
            return '';
        }

        return implode("\n", array_map(
            static fn (OperacionTercero $op): string => self::filaOperacion($op),
            $declaracion->operaciones
        ));
    }

    private static function assertMontoDosDecimales(float $value, string $campo): string
    {
        if (!is_finite($value) || $value < 0) {
            throw new \InvalidArgumentException("{$campo}: el monto debe ser un número finito mayor o igual a cero");
        }

        $redondeado = round($value * 100) / 100;
        if (abs($value - $redondeado) > 1e-9) {
            throw new \InvalidArgumentException("{$campo}: los montos deben tener como máximo 2 decimales (formato de pesos)");
        }

        return number_format($redondeado, 2, '.', '');
    }

    private static function celda(?string $val): string
    {
        return trim($val ?? '');
    }

    private static function filaOperacion(OperacionTercero $op): string
    {
        $iva16 = self::assertMontoDosDecimales($op->montoIva16, 'montoIva16');
        $iva0 = self::assertMontoDosDecimales($op->montoIva0, 'montoIva0');
        $exento = self::assertMontoDosDecimales($op->montoExento, 'montoExento');
        $retenido = self::assertMontoDosDecimales($op->montoRetenido, 'montoRetenido');
        $noDeduc = self::assertMontoDosDecimales($op->montoIvaNoDeduc, 'montoIvaNoDeduc');

        $campos = [
            $op->tipoTercero->value,
            $op->tipoOperacion->value,
            self::celda($op->rfc),
            self::celda($op->idFiscal),
            self::celda($op->nombreExtranjero),
            self::celda($op->paisResidencia),
            self::celda($op->nacionalidad),
            $iva16,
            $iva0,
            $exento,
            $retenido,
            $noDeduc,
        ];

        return implode('|', $campos);
    }
}
