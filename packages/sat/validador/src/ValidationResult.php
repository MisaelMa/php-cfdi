<?php

namespace Cfdi\Validador;

class ValidationResult
{
    public function __construct(
        public readonly bool $valid,
        /** @var ValidationIssue[] */
        public readonly array $errors,
        /** @var ValidationIssue[] */
        public readonly array $warnings,
        public readonly string $version,
    ) {
    }
}
