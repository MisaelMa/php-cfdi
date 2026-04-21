<?php

namespace Cfdi\Validador\Rules;

use Cfdi\Validador\CfdiData;
use Cfdi\Validador\ValidationIssue;

class Receptor
{
    private const RFC_REGEX_PM = '/^[A-Z&Ñ]{3}[0-9]{6}[A-Z0-9]{3}$/';
    private const RFC_REGEX_PF = '/^[A-Z&Ñ]{4}[0-9]{6}[A-Z0-9]{3}$/';

    /** @return ValidationIssue[] */
    public static function validate(CfdiData $data): array
    {
        return array_merge(
            self::checkRfc($data),
            self::checkUsoCFDI($data),
            self::checkDomicilioFiscal40($data),
            self::checkRegimenFiscalReceptor40($data),
        );
    }

    private static function isRfcValido(string $rfc): bool
    {
        if (in_array($rfc, ['XAXX010101000', 'XEXX010101000'], true)) return true;
        return (bool) preg_match(self::RFC_REGEX_PM, $rfc) || (bool) preg_match(self::RFC_REGEX_PF, $rfc);
    }

    private static function checkRfc(CfdiData $data): array
    {
        $rfc = $data->receptor['Rfc'] ?? '';
        if (empty($rfc)) {
            return [new ValidationIssue('CFDI401', 'RFC del Receptor es requerido', 'receptor.rfc', 'Receptor.Rfc')];
        }
        if (!self::isRfcValido($rfc)) {
            return [new ValidationIssue('CFDI402', "RFC del Receptor '{$rfc}' no tiene un formato valido", 'receptor.rfc', 'Receptor.Rfc')];
        }
        return [];
    }

    private static function checkUsoCFDI(CfdiData $data): array
    {
        if (empty(trim($data->receptor['UsoCFDI'] ?? ''))) {
            return [new ValidationIssue('CFDI403', 'UsoCFDI del Receptor es requerido', 'receptor.usoCFDI', 'Receptor.UsoCFDI')];
        }
        return [];
    }

    private static function checkDomicilioFiscal40(CfdiData $data): array
    {
        if ($data->version !== '4.0') return [];
        $domicilio = $data->receptor['DomicilioFiscalReceptor'] ?? '';
        if (empty(trim($domicilio))) {
            return [new ValidationIssue('CFDI404', 'DomicilioFiscalReceptor es requerido en CFDI 4.0', 'receptor.domicilioFiscal', 'Receptor.DomicilioFiscalReceptor')];
        }
        if (!preg_match('/^\d{5}$/', $domicilio)) {
            return [new ValidationIssue('CFDI405', "DomicilioFiscalReceptor '{$domicilio}' debe ser un codigo postal de 5 digitos", 'receptor.domicilioFiscal', 'Receptor.DomicilioFiscalReceptor')];
        }
        return [];
    }

    private static function checkRegimenFiscalReceptor40(CfdiData $data): array
    {
        if ($data->version !== '4.0') return [];
        if (empty(trim($data->receptor['RegimenFiscalReceptor'] ?? ''))) {
            return [new ValidationIssue('CFDI406', 'RegimenFiscalReceptor es requerido en CFDI 4.0', 'receptor.regimenFiscal', 'Receptor.RegimenFiscalReceptor')];
        }
        return [];
    }
}
