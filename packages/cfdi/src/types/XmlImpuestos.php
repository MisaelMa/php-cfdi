<?php

namespace Sat\Types;

use Sat\Interface\XmlImpuestosInterface;

class XmlImpuestos implements XmlImpuestosInterface
{
    public array $_attributes = [];

    public function getAttributes(): array
    {
        return $this->_attributes;
    }

    public function setAttributes(array $attributes): void
    {
        $this->_attributes = $attributes;
    }
}
