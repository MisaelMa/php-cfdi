<?php

declare(strict_types=1);

namespace Sat\Diot;

enum TipoTercero: string
{
    case ProveedorNacional = '04';
    case ProveedorExtranjero = '05';
    case ProveedorGlobal = '15';
}
