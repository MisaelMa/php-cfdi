<?php

declare(strict_types=1);

namespace Sat\Pacs;

enum PacProviderType: string
{
    case Finkok = 'finkok';
    case Custom = 'custom';
}
