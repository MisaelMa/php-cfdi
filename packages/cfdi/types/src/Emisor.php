<?php

declare(strict_types=1);

namespace Cfdi\Types;

/**
 * cfdi:Emisor (Node: XmlEmisor).
 */
interface Emisor
{
    public function getAttributes(): EmisorAttributes;
}

/**
 * Node: XmlEmisorAttribute
 */
interface EmisorAttributes
{
    public function getRfc(): string;

    public function getNombre(): string;

    /** @return string|int */
    public function getRegimenFiscal(): string|int;

    /** @return string|int|null */
    public function getFacAtrAdquirente(): string|int|null;
}
