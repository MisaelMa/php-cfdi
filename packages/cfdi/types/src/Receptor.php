<?php

declare(strict_types=1);

namespace Cfdi\Types;

/**
 * cfdi:Receptor (Node: XmlReceptor).
 */
interface Receptor
{
    public function getAttributes(): ReceptorAttributes;
}

/**
 * Node: XmlReceptorAttribute
 */
interface ReceptorAttributes
{
    public function getRfc(): string;

    public function getNombre(): string;

    public function getUsoCfdi(): string;

    public function getDomicilioFiscalReceptor(): string;

    public function getResidenciaFiscal(): ?string;

    public function getNumRegIdTrib(): ?string;

    /** @return string|int */
    public function getRegimenFiscalReceptor(): string|int;
}
