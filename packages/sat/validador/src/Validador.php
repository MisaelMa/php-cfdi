<?php

namespace Cfdi\Validador;

use Cfdi\Validador\Rules;

class Validador
{
    public function validate(string $xml): ValidationResult
    {
        $data = Parser::parse($xml);
        $allIssues = array_merge(
            Rules\Estructura::validate($data),
            Rules\Montos::validate($data),
            Rules\Emisor::validate($data),
            Rules\Receptor::validate($data),
            Rules\Conceptos::validate($data),
            Rules\Impuestos::validate($data),
            Rules\Timbre::validate($data),
            Rules\Sello::validate($data),
        );

        $errors = [];
        $warnings = [];
        foreach ($allIssues as $issue) {
            if (str_ends_with($issue->code, 'W')) {
                $warnings[] = $issue;
            } else {
                $errors[] = $issue;
            }
        }

        return new ValidationResult(
            valid: empty($errors),
            errors: $errors,
            warnings: $warnings,
            version: $data->version,
        );
    }

    public function validateFile(string $filePath): ValidationResult
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }
        $xml = file_get_contents($filePath);
        return $this->validate($xml);
    }
}
