<?php

namespace Cfdi\Retenciones;

/**
 * Clasificación por clave de retención (catálogo c_CveRetenc del SAT).
 * Los valores son ejemplos representativos; el catálogo oficial define el conjunto completo.
 */
enum TipoRetencion: string
{
    case Arrendamiento = '14';
    case Dividendos = '16';
    case Intereses = '17';
    case Fideicomiso = '18';
    case EnajenacionAcciones = '19';
    case Otro = '99';
}
