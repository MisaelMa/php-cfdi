<?php

namespace Cfdi\Rfc;

class RfcValidator
{
    public static function getType(string $rfc): string
    {
        return Constants::SPECIAL_CASES[$rfc]
            ?? Constants::RFC_TYPE_FOR_LENGTH[strlen($rfc)]
            ?? '';
    }

    public static function hasForbiddenWords(string $rfc): bool
    {
        $prefix = mb_substr($rfc ?: '', 0, 4);
        return in_array($prefix, Constants::FORBIDDEN_WORD, true);
    }

    private static function parseInput(string $input): string
    {
        $trimmed = trim($input);
        $upper = mb_strtoupper($trimmed);
        return preg_replace('/[^0-9A-ZÑ&]/u', '', $upper);
    }

    private static function validateDate(string $rfc): bool
    {
        $dateStr = substr(substr($rfc, 0, -3), -6);
        $year = substr($dateStr, 0, 2);
        $month = substr($dateStr, 2, 2);
        $day = substr($dateStr, 4, 2);

        $monthInt = (int) $month;
        $dayInt = (int) $day;

        if ($monthInt < 1 || $monthInt > 12 || $dayInt < 1 || $dayInt > 31) {
            return false;
        }

        return checkdate($monthInt, $dayInt, (int) ("20{$year}"));
    }

    private static function validateVerificationDigit(string $rfc): bool
    {
        $digit = substr($rfc, -1);
        $expected = CheckDigit::calculate($rfc);
        return $expected === $digit;
    }

    /**
     * @return array{isValid: bool, type: string, rfc: string}
     */
    public static function validate(string $input): array
    {
        $rfc = self::parseInput($input);
        $result = [
            'isValid' => false,
            'type' => '',
            'rfc' => $rfc ?: '',
        ];

        $hasValidFormat = (bool) preg_match(Constants::RFC_REGEXP, $rfc);

        if ($hasValidFormat && self::validateDate($rfc) && self::validateVerificationDigit($rfc) && !self::hasForbiddenWords($rfc)) {
            $result['isValid'] = true;
            $result['type'] = self::getType($rfc);
        }

        return $result;
    }
}
