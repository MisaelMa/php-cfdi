<?php

namespace Cfdi\Transform;

class ChildRule
{
    public function __construct(
        public readonly string $type = 'child',
        public readonly string $select = '',
        public readonly bool $forEach = false,
        /** @var (AttrRule|TextRule)[] */
        public readonly array $inline = [],
        public readonly bool $applyTemplates = false,
        public readonly ?string $condition = null,
        public readonly bool $wildcard = false,
        public readonly bool $descendant = false,
    ) {
    }
}
