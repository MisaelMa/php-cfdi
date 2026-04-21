<?php

namespace Cfdi\Rfc;

class RfcFaker
{
    private const VOWELS = 'AEIOU';
    private const LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const HOMOCLAVE_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    private static function pick(string $chars): string
    {
        return $chars[random_int(0, strlen($chars) - 1)];
    }

    private static function randomInt(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    private static function pad2(int $n): string
    {
        return str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    }

    private static function randomDateStr(): string
    {
        $year = self::randomInt(30, 99);
        $month = self::randomInt(1, 12);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, 1900 + $year);
        $day = self::randomInt(1, $daysInMonth);
        return self::pad2($year) . self::pad2($month) . self::pad2($day);
    }

    private static function randomHomoclave2(): string
    {
        return self::pick(self::HOMOCLAVE_CHARS) . self::pick(self::HOMOCLAVE_CHARS);
    }

    private static function hasForbiddenPrefix(string $prefix): bool
    {
        return in_array(mb_strtoupper(mb_substr($prefix, 0, 4)), Constants::FORBIDDEN_WORD, true);
    }

    private static function personaFisicaPrefix(): string
    {
        do {
            $prefix = self::pick(self::LETTERS) . self::pick(self::VOWELS)
                . self::pick(self::LETTERS) . self::pick(self::LETTERS);
        } while (self::hasForbiddenPrefix($prefix));

        return $prefix;
    }

    private static function personaMoralPrefix(): string
    {
        do {
            $prefix = self::pick(self::LETTERS) . self::pick(self::LETTERS) . self::pick(self::LETTERS);
        } while (self::hasForbiddenPrefix($prefix . 'A'));

        return $prefix;
    }

    public static function persona(): string
    {
        do {
            $prefix = self::personaFisicaPrefix();
            $date = self::randomDateStr();
            $homo2 = self::randomHomoclave2();
            $base = $prefix . $date . $homo2;
            $digit = CheckDigit::calculate($base . '0');
            $rfc = $base . $digit;
        } while (strlen($rfc) !== 13);

        return $rfc;
    }

    public static function moral(): string
    {
        do {
            $prefix = self::personaMoralPrefix();
            $date = self::randomDateStr();
            $homo2 = self::randomHomoclave2();
            $base = $prefix . $date . $homo2;
            $digit = CheckDigit::calculate($base . '0');
            $rfc = $base . $digit;
        } while (strlen($rfc) !== 12);

        return $rfc;
    }
}
