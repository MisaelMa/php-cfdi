<?php

namespace Cfdi\Transform;

class ParsedTemplate
{
    public function __construct(
        public readonly string $match,
        /** @var (AttrRule|TextRule|ChildRule)[] */
        public readonly array $rules,
    ) {
    }
}
