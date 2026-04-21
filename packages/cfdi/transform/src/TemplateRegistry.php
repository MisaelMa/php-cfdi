<?php

namespace Cfdi\Transform;

class TemplateRegistry
{
    public function __construct(
        /** @var array<string, ParsedTemplate> */
        public readonly array $templates,
        /** @var array<string, string> */
        public readonly array $namespaces,
    ) {
    }
}
