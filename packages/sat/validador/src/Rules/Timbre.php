<?php

namespace Cfdi\Validador\Rules;

use Cfdi\Validador\CfdiData;
use Cfdi\Validador\ValidationIssue;

class Timbre
{
    /** @return ValidationIssue[] */
    public static function validate(CfdiData $data): array
    {
        if ($data->timbre === null) return [];
        $issues = [];
        $uuid = $data->timbre['uuid'];
        $fechaTimbrado = $data->timbre['fechaTimbrado'];
        $version = $data->timbre['version'];

        if (!preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid)) {
            $issues[] = new ValidationIssue('CFDI701', "UUID del TimbreFiscalDigital '{$uuid}' no tiene formato valido", 'timbre.uuid', 'Complemento.TimbreFiscalDigital.UUID');
        }
        if (!empty($fechaTimbrado) && !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $fechaTimbrado)) {
            $issues[] = new ValidationIssue('CFDI702', "FechaTimbrado '{$fechaTimbrado}' no tiene formato ISO 8601", 'timbre.fechaTimbrado', 'Complemento.TimbreFiscalDigital.FechaTimbrado');
        }
        if (!empty($version) && $version !== '1.1') {
            $issues[] = new ValidationIssue('CFDI703', "Version del TimbreFiscalDigital debe ser '1.1', se encontro '{$version}'", 'timbre.version', 'Complemento.TimbreFiscalDigital.Version');
        }
        return $issues;
    }
}
