<?php

declare(strict_types=1);

namespace Sat\Opinion;

/**
 * Resultado de la opinión de cumplimiento (32-D) del SAT.
 *
 * @see https://www.sat.gob.mx/aplicacion/operacion/66288/genera-tu-constancia-de-situacion-fiscal
 */
enum ResultadoOpinion: string
{
    case Positivo = 'Positivo';
    case Negativo = 'Negativo';
    case EnSuspenso = 'En suspenso';
    case InscritoSinObligaciones = 'Inscrito sin obligaciones';
    case NoInscrito = 'No inscrito';
}
