<?php

namespace Cfdi\Descarga;

enum TipoSolicitud: string
{
    case CFDI = 'CFDI';
    case Metadata = 'Metadata';
}
