<?php

namespace Cfdi\Rfc;

class Constants
{
    public const RFC_REGEXP = '/^([A-ZÑ&]{3,4})([0-9]{6})([A-Z0-9]{3})$/';

    public const RFC_TYPE_FOR_LENGTH = [
        12 => 'company',
        13 => 'person',
    ];

    public const SPECIAL_CASES = [
        'XEXX010101000' => 'foreign',
        'XAXX010101000' => 'generic',
    ];

    public const FORBIDDEN_WORD = [
        'BUEI', 'BUEY', 'CACA', 'CACO', 'CAGA', 'CAGO', 'CAKA', 'CAKO',
        'COGE', 'COJA', 'COJE', 'COJI', 'COJO', 'CULO', 'FETO', 'GUEY',
        'JOTO', 'KACA', 'KACO', 'KAGA', 'KAGO', 'KOGE', 'KOJO', 'KAKA',
        'KULO', 'MAME', 'MAMO', 'MEAR', 'MEAS', 'MEON', 'MION', 'MOCO',
        'MULA', 'PEDA', 'PEDO', 'PENE', 'PUTA', 'PUTO', 'QULO', 'RATA',
        'RUIN',
    ];
}
