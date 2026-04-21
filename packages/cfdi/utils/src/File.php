<?php

namespace Cfdi\Utils;

class File
{
    public static function isPath(string $input): bool
    {
        return (bool) preg_match('/[\/\\\\]|(\.\w+)$/', $input);
    }
}
