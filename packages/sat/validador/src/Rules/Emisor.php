<?php

namespace Cfdi\Validador\Rules;

use Cfdi\Validador\CfdiData;
use Cfdi\Validador\ValidationIssue;

class Emisor
{
    private const RFC_REGEX_PM = '/^[A-Z&Ñ]{3}[0-9]{6}[A-Z0-9]{3}$/';
    private const RFC_REGEX_PF = '/^[A-Z&Ñ]{4}[0-9]{6}[A-Z0-9]{3}$/';

    /** @return ValidationIssue[] */
    public static function validate(CfdiData $data): array
    {
        return array_merge(
            self::checkRfc($data),
            self::checkNombre($data),
            self::checkRegimenFiscal($data),
        );
    }

    private static function isRfcValido(string $rfc): bool
    {
        if (in_array($rfc, ['XAXX010101000', 'XEXX010101000'], true)) return true;
        return (bool) preg_match(self::RFC_REGEX_PM, $rfc) || (bool) preg_match(self::RFC_REGEX_PF, $rfc);
    }

    private static function checkRfc(CfdiData $data): array
    {
        $rfc = $data->emisor['Rfc'] ?? '';
        if (empty($rfc)) {
            return [new ValidationIssue('CFDI301', 'RFC del Emisor es requerido', 'emisor.rfc', 'Emisor.Rfc')];
        }
        if (!self::isRfcValido($rfc)) {
            return [new ValidationIssue('CFDI302', "RFC del Emisor '{$rfc}' no tiene un formato valido", 'emisor.rfc', 'Emisor.Rfc')];
        }
        return [];
    }

    private static function checkNombre(CfdiData $data): array
    {
        if ($data->version === '4.0' && empty(trim($data->emisor['Nombre'] ?? ''))) {
            return [new ValidationIssue('CFDI303', 'Nombre del Emisor es requerido en CFDI 4.0', 'emisor.nombre', 'Emisor.Nombre')];
        }
        return [];
    }

    private static function checkRegimenFiscal(CfdiData $data): array
    {
        if (empty(trim($data->emisor['RegimenFiscal'] ?? ''))) {
            return [new ValidationIssue('CFDI304', 'RegimenFiscal del Emisor es requerido', 'emisor.regimenFiscal', 'Emisor.RegimenFiscal')];
        }
        return [];
    }
}
