<?php

namespace Renapo\Curp;

class BadCurpFormat extends \InvalidArgumentException
{
    public function __construct(string $curp)
    {
        parent::__construct("'{$curp}' is an invalid curp");
    }
}

class Curp
{
    public static function parseInput(string $input): string
    {
        $cleaned = strtoupper(trim($input));
        return preg_replace('/[^0-9A-Z]/', '', $cleaned);
    }

    public static function validateDate(string $curp): bool
    {
        $dateStr = substr($curp, 4, 6);
        $year = substr($dateStr, 0, 2);
        $month = substr($dateStr, 2, 2);
        $day = substr($dateStr, 4, 2);
        $date = @strtotime("20{$year}-{$month}-{$day}");
        return $date !== false;
    }

    public static function validateCheckDigit(string $curp): bool
    {
        $digit = substr($curp, -1);
        $expected = CheckDigit::checkDigit($curp);
        return $expected === $digit;
    }

    public static function validateState(string $curp): bool
    {
        $state = substr($curp, 11, 2);
        return in_array($state, Constants::STATES, true);
    }

    public static function getState(string $curp): string
    {
        if (preg_match(Constants::REGEX_CURP, $curp, $match)) {
            return $match[3] ?? '0';
        }
        return '0';
    }

    public static function hasForbiddenWords(string $curp): bool
    {
        $prefix = substr($curp, 0, 4);
        return in_array($prefix, Constants::FORBIDDEN_WORDS, true);
    }

    public static function validateLocal(string $input): array
    {
        $curp = self::parseInput($input);
        $result = [
            'isValid' => false,
            'rfc' => $curp ?: '',
            'error' => [],
        ];
        $match = preg_match(Constants::REGEX_CURP, $curp);
        if ($match && !self::hasForbiddenWords($curp)) {
            $result['isValid'] = true;
        }
        return $result;
    }

    public static function validate(string $input): array
    {
        $curp = self::parseInput($input);
        $result = [
            'isValid' => false,
            'rfc' => $curp ?: '',
        ];
        $match = preg_match(Constants::REGEX_CURP, $curp);
        if ($match && !self::hasForbiddenWords($curp) &&
            self::validateDate($curp) && self::validateState($curp) &&
            self::validateCheckDigit($curp)) {
            $result['isValid'] = true;
        }
        return $result;
    }
}
