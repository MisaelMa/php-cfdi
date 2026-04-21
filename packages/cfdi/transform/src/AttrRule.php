<?php

namespace Cfdi\Transform;

class AttrRule
{
    public function __construct(
        public readonly string $type = 'attr',
        public readonly string $name = '',
        public readonly bool $required = false,
    ) {
    }
}
