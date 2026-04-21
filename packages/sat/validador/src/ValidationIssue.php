<?php

namespace Cfdi\Validador;

class ValidationIssue
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly string $rule,
        public readonly ?string $field = null,
    ) {
    }
}
