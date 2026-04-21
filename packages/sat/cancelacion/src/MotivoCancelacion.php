<?php

declare(strict_types=1);

namespace Sat\Cancelacion;

/**
 * Motivo de cancelación según el SAT (Anexo 20).
 *
 * @see https://www.sat.gob.mx/consultas/91447/consulta-de-cancelacion-de-cfdi
 */
enum MotivoCancelacion: string
{
    /** Comprobante emitido con errores con relación */
    case ConRelacion = '01';
    /** Comprobante emitido con errores sin relación */
    case SinRelacion = '02';
    /** No se llevó a cabo la operación */
    case NoOperacion = '03';
    /** Operación nominativa relacionada en la factura global */
    case FacturaGlobal = '04';
}
