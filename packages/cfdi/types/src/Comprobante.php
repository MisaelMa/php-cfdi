<?php

declare(strict_types=1);

namespace Cfdi\Types;

/**
 * Root CFDI document (Node: XmlCdfi) — XML declaration + Comprobante element.
 */
interface CfdiDocument
{
    public function getDeclaration(): XmlDeclaration;

    public function getComprobante(): Comprobante;
}

interface XmlDeclaration
{
    public function getVersion(): string;

    public function getEncoding(): string;
}

/**
 * cfdi:Comprobante structure (Node: XmlComprobante).
 */
interface Comprobante
{
    public function getAttributes(): ComprobanteAttributes;

    /**
     * cfdi:InformacionGlobal — shape depends on CFDI version / use case.
     *
     * @return array<string, mixed>|null
     */
    public function getInformacionGlobal(): ?array;

    public function getCfdiRelacionados(): ?CfdiRelacionados;

    public function getEmisor(): ?Emisor;

    public function getReceptor(): ?Receptor;

    public function getConceptos(): Conceptos;

    public function getImpuestos(): ?Impuestos;

    /**
     * cfdi:Complemento — open set of complement payloads (Node: XmlComplements).
     *
     * @return array<string, mixed>|null
     */
    public function getComplemento(): ?array;
}

/**
 * _attributes on cfdi:Comprobante (Node: XmlComprobanteAttributes).
 *
 * Extends CFDI body fields, signature fields, xsi/xmlns instance attributes,
 * optional complement xmlns:* declarations, and allows extra keys (AnyKey).
 */
interface ComprobanteAttributes extends CfdiComprobanteFields, ComprobanteSignatureFields, XmlInstanceFields
{
    /**
     * Optional xmlns:* for complements and any attributes not covered by explicit getters
     * (Node: XmlComplementsAttributes & AnyKey), e.g. ['xmlns:pago20' => 'http://...'].
     *
     * @return array<string, string|null>
     */
    public function getSupplementalAttributes(): array;
}

/** Node: CFDIComprobante */
interface CfdiComprobanteFields
{
    public function getVersion(): ?string;

    public function getSerie(): ?string;

    public function getFolio(): ?string;

    public function getFecha(): string;

    /** Catalog code or literal — serialized as string in XML */
    public function getFormaPago(): null|string|int;

    public function getCondicionesDePago(): ?string;

    /** @return string|int|float */
    public function getSubTotal(): string|int|float;

    /** @return string|int|float|null */
    public function getDescuento(): string|int|float|null;

    public function getMoneda(): string;

    public function getTipoCambio(): ?string;

    /** @return string|int|float */
    public function getTotal(): string|int|float;

    /** TipoDeComprobante catalog */
    public function getTipoDeComprobante(): string;

    /** Exportación catalog */
    public function getExportacion(): string;

    /** MetodoPago catalog */
    public function getMetodoPago(): ?string;

    public function getLugarExpedicion(): string;

    public function getConfirmacion(): ?string;
}

/** Node: ComprobanteSignature */
interface ComprobanteSignatureFields
{
    public function getNoCertificado(): string;

    public function getCertificado(): ?string;

    public function getSello(): ?string;
}

/** Node: schemaLocation / xsi / xs on root */
interface XmlInstanceFields
{
    public function getXmlnsXsi(): ?string;

    public function getXmlnsXs(): ?string;

    public function getXsiSchemaLocation(): ?string;
}

interface CfdiRelacionados
{
    public function getAttributes(): ?CfdiRelacionadosAttributes;

    /**
     * @return list<CfdiRelacionado>|null
     */
    public function getCfdiRelacionado(): ?array;
}

interface CfdiRelacionadosAttributes
{
    public function getTipoRelacion(): string;
}

interface CfdiRelacionado
{
    public function getAttributes(): ?CfdiRelacionadoAttributes;
}

interface CfdiRelacionadoAttributes
{
    public function getUuid(): string;
}
