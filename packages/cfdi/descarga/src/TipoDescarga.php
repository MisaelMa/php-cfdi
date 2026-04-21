<?php

namespace Cfdi\Descarga;

enum TipoDescarga: string
{
    case Emitidos = 'RfcEmisor';
    case Recibidos = 'RfcReceptor';
}
