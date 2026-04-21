<?php

namespace Cfdi\Validador\Rules;

use Cfdi\Validador\CfdiData;
use Cfdi\Validador\ValidationIssue;

class Sello
{
    /** @return ValidationIssue[] */
    public static function validate(CfdiData $data): array
    {
        return array_merge(
            self::checkNoCertificado($data),
            self::checkSello($data),
            self::checkCertificado($data),
        );
    }

    private static function isBase64Valido(string $val): bool
    {
        if (empty(trim($val))) return true;
        return (bool) preg_match('/^[A-Za-z0-9+\/]*={0,2}$/', $val) && strlen($val) % 4 === 0;
    }

    private static function checkNoCertificado(CfdiData $data): array
    {
        $noCert = $data->comprobante['NoCertificado'] ?? null;
        if ($noCert === null) {
            return [new ValidationIssue('CFDI801', 'NoCertificado es requerido en el Comprobante', 'sello.noCertificado', 'NoCertificado')];
        }
        if ($noCert !== '' && !preg_match('/^\d{20}$/', $noCert)) {
            return [new ValidationIssue('CFDI802', "NoCertificado '{$noCert}' debe tener exactamente 20 digitos", 'sello.noCertificado', 'NoCertificado')];
        }
        return [];
    }

    private static function checkSello(CfdiData $data): array
    {
        $sello = $data->comprobante['Sello'] ?? null;
        if ($sello === null) {
            return [new ValidationIssue('CFDI803', 'Sello es requerido en el Comprobante', 'sello.sello', 'Sello')];
        }
        if ($sello !== '' && !self::isBase64Valido($sello)) {
            return [new ValidationIssue('CFDI804', 'Sello no es una cadena base64 valida', 'sello.sello', 'Sello')];
        }
        return [];
    }

    private static function checkCertificado(CfdiData $data): array
    {
        $cert = $data->comprobante['Certificado'] ?? null;
        if ($cert === null) {
            return [new ValidationIssue('CFDI805', 'Certificado es requerido en el Comprobante', 'sello.certificado', 'Certificado')];
        }
        if ($cert !== '' && !self::isBase64Valido($cert)) {
            return [new ValidationIssue('CFDI806', 'Certificado no es una cadena base64 valida', 'sello.certificado', 'Certificado')];
        }
        return [];
    }
}
