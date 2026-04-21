<?php

declare(strict_types=1);

namespace Cfdi\Types;

/**
 * cfdi:Conceptos wrapper (Node: XmlConcepto).
 */
interface Conceptos
{
    /**
     * @return list<Concepto>
     */
    public function getConcepto(): array;
}

/**
 * Single cfdi:Concepto line (Node: XmlConceptoProperties).
 */
interface Concepto
{
    public function getAttributes(): ConceptoAttributes;

    public function getImpuestos(): ?Impuestos;

    /**
     * cfdi:ComplementoConcepto (Node: XmlComplementsConcepts).
     *
     * @return array<string, mixed>|null
     */
    public function getComplementoConcepto(): ?array;

    public function getParte(): ?ConceptoParte;

    /**
     * @return list<InformacionAduanera>|null
     */
    public function getInformacionAduanera(): ?array;
}

/**
 * Node: XmlConceptoAttributes — ObjetoImp '01' | '02' | '03'
 */
interface ConceptoAttributes
{
    public function getClaveProdServ(): string;

    public function getNoIdentificacion(): ?string;

    /** @return string|int|float */
    public function getCantidad(): string|int|float;

    public function getClaveUnidad(): string;

    public function getUnidad(): ?string;

    public function getDescripcion(): string;

    /** @return string|int|float */
    public function getValorUnitario(): string|int|float;

    /** @return string|int|float */
    public function getImporte(): string|int|float;

    /** @return string|int|float|null */
    public function getDescuento(): string|int|float|null;

    public function getObjetoImp(): string;
}

/**
 * Node: XmlConceptoParte (single Parte; XML may repeat — exposed as list on Concepto).
 */
interface ConceptoParte
{
    public function getAttributes(): ConceptoParteAttributes;

    /**
     * @return list<InformacionAduanera>|null
     */
    public function getInformacionAduanera(): ?array;
}

/**
 * Node: XmlConceptParteAttributes
 */
interface ConceptoParteAttributes
{
    /** @return string|int */
    public function getClaveProdServ(): string|int;

    /** @return string|int|null */
    public function getNoIdentificacion(): string|int|null;

    /** @return string|int|float */
    public function getCantidad(): string|int|float;

    /** @return string|int|null */
    public function getUnidad(): string|int|null;

    /** @return string|int */
    public function getDescripcion(): string|int;

    /** @return string|int|float|null */
    public function getValorUnitario(): string|int|float|null;

    /** @return string|int|float|null */
    public function getImporte(): string|int|float|null;
}

/**
 * Node: InformacionAduanera
 */
interface InformacionAduanera
{
    public function getAttributes(): InformacionAduaneraAttributes;
}

interface InformacionAduaneraAttributes
{
    public function getNumeroPedimento(): string;
}

/**
 * Node: XmlConceptoTercerosAttributes (por cuenta de terceros on concept).
 */
interface ConceptoTercerosAttributes
{
    /** @return string|int */
    public function getRfcACuentaTerceros(): string|int;

    /** @return string|int */
    public function getNombreACuentaTerceros(): string|int;

    /** @return string|int */
    public function getRegimenFiscalACuentaTerceros(): string|int;

    /** @return string|int */
    public function getDomicilioFiscalACuentaTerceros(): string|int;
}
