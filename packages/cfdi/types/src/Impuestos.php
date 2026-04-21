<?php

declare(strict_types=1);

namespace Cfdi\Types;

/**
 * cfdi:Impuestos at comprobante or concepto level (Node: XmlImpuestos).
 */
interface Impuestos
{
    public function getAttributes(): ImpuestosTotalesAttributes;

    public function getTraslados(): ?Traslados;

    public function getRetenciones(): ?Retenciones;
}

/**
 * Node: XmlImpuestosTrasladados — totals on impuestos element
 */
interface ImpuestosTotalesAttributes
{
    /** @return string|int|float|null */
    public function getTotalImpuestosRetenidos(): string|int|float|null;

    /** @return string|int|float|null */
    public function getTotalImpuestosTrasladados(): string|int|float|null;
}

/**
 * Node: XmlTranslado
 */
interface Traslados
{
    /**
     * @return list<Traslado>
     */
    public function getTraslado(): array;
}

interface Traslado
{
    public function getAttributes(): TrasladoRetencionBaseAttributes;
}

/**
 * Node: XmlRetenciones
 */
interface Retenciones
{
    /**
     * @return list<Retencion>
     */
    public function getRetencion(): array;
}

interface Retencion
{
    public function getAttributes(): TrasladoRetencionBaseAttributes;
}

/**
 * Node: XmlTranRentAttributesProperties (traslado / retención line).
 */
interface TrasladoRetencionBaseAttributes
{
    /** @return string|int|float|null */
    public function getBase(): string|int|float|null;

    /** @return string|int */
    public function getImpuesto(): string|int;

    public function getTipoFactor(): string;

    /** @return string|int|float|null */
    public function getTasaOCuota(): string|int|float|null;

    /** @return string|int|float|null */
    public function getImporte(): string|int|float|null;
}
