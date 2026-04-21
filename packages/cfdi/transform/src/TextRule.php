<?php

namespace Cfdi\Transform;

class TextRule
{
    public function __construct(
        public readonly string $type = 'text',
        public readonly string $select = '',
        public readonly bool $required = false,
    ) {
    }
}
