<?php

namespace Renapo\Curp;

class CheckDigit
{
    private const VALUES_MAP = [
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4,
        '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14,
        'F' => 15, 'G' => 16, 'H' => 17, 'I' => 18, 'J' => 19,
        'K' => 20, 'L' => 21, 'M' => 22, 'N' => 23, 'Ñ' => 24,
        'O' => 25, 'P' => 26, 'Q' => 27, 'R' => 28, 'S' => 29,
        'T' => 30, 'U' => 31, 'V' => 32, 'W' => 33, 'X' => 34,
        'Y' => 35, 'Z' => 36,
    ];

    private static function getScore(string $string): int
    {
        $sum = 0;
        $chars = mb_str_split($string);
        foreach ($chars as $i => $char) {
            $index = 18 - $i;
            $value = self::VALUES_MAP[$char] ?? 0;
            $sum += $value * $index;
        }
        return $sum;
    }

    public static function checkDigit(string $curp): string
    {
        $base = mb_substr($curp, 0, -1);
        $score = self::getScore($base);
        $mod = $score % 10;
        if ($mod === 0) return '0';
        return (string) (10 - $mod);
    }
}
