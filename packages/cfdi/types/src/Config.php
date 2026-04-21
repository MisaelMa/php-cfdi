<?php

declare(strict_types=1);

namespace Cfdi\Types;

/**
 * XML / tooling options (Node: types.Config) plus optional certificate / sello fields
 * (Node: Cer + ComprobanteSignature).
 */
interface Config
{
    public function isDebug(): bool;

    public function isCompact(): bool;

    /**
     * @return array<string, mixed>|null
     */
    public function getCustomTags(): ?array;

    public function getSchema(): ?ConfigSchema;

    public function getSaxon(): ?ConfigSaxonHe;

    public function getXslt(): ?ConfigXsltSheet;

    public function getNoCertificado(): ?string;

    public function getCertificado(): ?string;

    public function getSello(): ?string;

    public function getCertificateData(): ?CertificateData;
}

interface ConfigSchema
{
    public function getPath(): string;
}

interface ConfigSaxonHe
{
    public function getBinary(): string;
}

interface ConfigXsltSheet
{
    public function getPath(): string;
}

/**
 * Certificate material (.cer) aligned with Node Cer — nocer + cer PEM/base64 payload.
 */
interface CertificateData
{
    /** PEM or raw certificate contents */
    public function getCer(): string;

    /** Certificate serial number as used in NoCertificado */
    public function getNocer(): string;
}
