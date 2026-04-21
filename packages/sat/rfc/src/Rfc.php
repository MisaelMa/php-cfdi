<?php

namespace Cfdi\Rfc;

class Rfc
{
    private const SPECIAL_RFC_VALUES = ['XEXX010101000', 'XAXX010101000'];

    private function __construct(private readonly string $value)
    {
    }

    /**
     * Factory principal: valida y lanza InvalidRfcError si el RFC es invalido.
     * Los RFCs especiales (generico y extranjero) son aceptados por definicion.
     */
    public static function of(string $rfc): self
    {
        $normalized = mb_strtoupper(trim($rfc));

        if (in_array($normalized, self::SPECIAL_RFC_VALUES, true)) {
            return new self($normalized);
        }

        $result = RfcValidator::validate($rfc);

        if (!$result['isValid']) {
            throw new InvalidRfcError($rfc);
        }

        return new self($result['rfc']);
    }

    /**
     * Factory segura: retorna null si el RFC es invalido.
     */
    public static function parse(string $rfc): ?self
    {
        try {
            return self::of($rfc);
        } catch (InvalidRfcError) {
            return null;
        }
    }

    public static function isValid(string $rfc): bool
    {
        $normalized = mb_strtoupper(trim($rfc));

        if (in_array($normalized, self::SPECIAL_RFC_VALUES, true)) {
            return true;
        }

        return RfcValidator::validate($rfc)['isValid'];
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function isFisica(): bool
    {
        return strlen($this->value) === 13 && !$this->isGeneric() && !$this->isForeign();
    }

    public function isMoral(): bool
    {
        return strlen($this->value) === 12;
    }

    public function isGeneric(): bool
    {
        return $this->value === 'XAXX010101000';
    }

    public function isForeign(): bool
    {
        return $this->value === 'XEXX010101000';
    }

    /**
     * Extrae la fecha de nacimiento/constitucion codificada en el RFC (YYMMDD).
     * Retorna null para RFCs especiales (generico y extranjero).
     */
    public function obtainDate(): ?\DateTimeImmutable
    {
        if ($this->isGeneric() || $this->isForeign()) {
            return null;
        }

        $dateStr = strlen($this->value) === 12
            ? substr($this->value, 3, 6)
            : substr($this->value, 4, 6);

        $year = (int) substr($dateStr, 0, 2);
        $month = (int) substr($dateStr, 2, 2);
        $day = (int) substr($dateStr, 4, 2);

        $currentYear = (int) date('y');
        $century = $year <= $currentYear ? 2000 : 1900;

        $fullYear = $century + $year;

        if (!checkdate($month, $day, $fullYear)) {
            return null;
        }

        return new \DateTimeImmutable("{$fullYear}-{$month}-{$day}");
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
