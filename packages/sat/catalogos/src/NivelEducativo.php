<?php

namespace Cfdi\Catalogos;

enum NivelEducativo: string
{
    case PREESCOLAR = 'Preescolar';
    case PRIMARIA = 'Primaria';
    case SECUNDARIA = 'Secundaria';
    case PROFESIONAL_TECNICO = 'Profesional técnico';
    case BACHILLERATO = 'Bachillerato o su equivalente';
}
