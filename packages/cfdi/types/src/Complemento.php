<?php

declare(strict_types=1);

namespace Cfdi\Types;

/**
 * Complement builder contract (Node: abstract class Complemento<T>).
 *
 * Implementations hold namespace URI, QName key, XSD location, and the payload
 * merged into cfdi:Complemento.
 */
interface Complemento
{
    /**
     * Complement body (Node: complemento), e.g. ['_attributes' => [...], 'child' => ...].
     *
     * @return array<string, mixed>
     */
    public function getComplementPayload(): array;

    /** QName e.g. tfd:TimbreFiscalDigital */
    public function getKey(): string;

    /** Target namespace URI */
    public function getXmlns(): string;

    /**
     * Pairs xmlns URI + XSD URL (Node: schemaLocation private array).
     *
     * @return list<string>
     */
    public function getSchemaLocation(): array;

    /** Prefix extracted from key (before ':') */
    public function getXmlnsKey(): string;
}

/**
 * Result of Complemento::getComplement() in Node (ComplementsReturn).
 */
interface ComplementoBuildResult
{
    /**
     * @return array<string, mixed>
     */
    public function getComplement(): array;

    public function getKey(): string;

    public function getXmlns(): string;

    public function getXmlnsKey(): string;

    /**
     * @return list<string>
     */
    public function getSchemaLocation(): array;
}
