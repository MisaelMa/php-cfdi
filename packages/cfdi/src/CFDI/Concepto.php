<?php

namespace Sat\Cfdi;

use Sat\Cfdi\BaseImpuestos;

/* use Cfdi\Complementos\ComlementTypeConcept;
use Cfdi\Complementos\ComplementProperties;
use Cfdi\Complementos\XmlComplementsConcepts;
use Cfdi\Types\InformacionAduanera;
use Cfdi\Types\XmlConceptParteAttributes;
use Cfdi\Types\XmlConceptoAttributes;
use Cfdi\Types\XmlConceptoProperties;
use Cfdi\Types\XmlConceptoTercerosAttributes;
use Cfdi\Types\XmlTranRentAttributesProperties;
use Cfdi\BaseImpuestos;
use Cfdi\Xsd\Schema; */

/**
 *
 */
class Concepto extends BaseImpuestos
{
  private bool $existComplemnt = false;
  private array $complementProperties = [];
  private array $concepto = [];

  /**
   * constructor
   *
   * @param XmlConceptoAttributes $concepto
   */
  public function __construct(array $concepto = [])
  {
    parent::__construct();
    $this->existComplemnt = false;
    $this->concepto['_attributes'] = $concepto;
  }

  /**
   * complemento
   *
   * @param ComlementTypeConcept $data
   */
  public function complemento(array $data): void
  {
    if (!isset($this->concepto['cfdi:ComplementoConcepto'])) {
      $this->concepto['cfdi:ComplementoConcepto'] = [];
    }
    $this->existComplemnt = true;
    $complement = $data->getComplement();
    $this->complementProperties['key'] = $complement['key'];
    $this->complementProperties['xmlns'] = $complement['xmlns'];
    $this->complementProperties['xmlnskey'] = $complement['xmlnskey'];
    $this->complementProperties['schemaLocation'] = $complement['schemaLocation'];
    $this->concepto['cfdi:ComplementoConcepto'][$complement['key']] = $complement['complement'];
  }

  /**
   * terceros
   *
   * @param XmlConceptoTercerosAttributes $cuenta
   */
  public function terceros(array $cuenta): self
  {
    $this->concepto['cfdi:ACuentaTerceros'] = [
      '_attributes' => $cuenta
    ];
    return $this;
  }

  /**
   * predial
   *
   * @param string $cuenta
   */
  public function predial(string $cuenta): self
  {
    $pre = [
      'Numero' => $cuenta
    ];
    $this->concepto['cfdi:CuentaPredial'] = [
      '_attributes' => $pre
    ];
    return $this;
  }

  /**
   * parte
   *
   * @param XmlConceptParteAttributes $parte
   */
  public function parte(array $parte): self
  {
    $cloneParte = [
      'Cantidad' => floatval($parte->Cantidad),
      'ValorUnitario' => floatval($parte->ValorUnitario),
      'Importe' => floatval($parte->ValorUnitario)
    ] + (array)$parte;

    $this->concepto['cfdi:Parte'] = [
      '_attributes' => $cloneParte
    ];
    return $this;
  }

  private function aduana(string $pedimento): array
  {
    $informacionAduanera = [
      'NumeroPedimento' => $pedimento
    ];

    return [
      '_attributes' => $informacionAduanera
    ];
  }

  public function setParteInformacionAduanera(string $pedimento): self
  {
    if (!isset($this->concepto['cfdi:Parte'])) {
      error_log('utilize primero parte');
      return $this;
    }
    if (!isset($this->concepto['cfdi:Parte']['cfdi:InformacionAduanera'])) {
      $this->concepto['cfdi:Parte']['cfdi:InformacionAduanera'] = [];
    }
    $this->concepto['cfdi:Parte']['cfdi:InformacionAduanera'][] = $this->aduana($pedimento);
    return $this;
  }

  /**
   * aduana
   *
   * @param string $pedimento
   */
  public function InformacionAduanera(string $pedimento): self
  {
    if (!isset($this->concepto['cfdi:InformacionAduanera'])) {
      $this->concepto['cfdi:InformacionAduanera'] = [];
    }
    $this->concepto['cfdi:InformacionAduanera'][] = $this->aduana($pedimento);
    return $this;
  }

  /**
   * traslado
   *
   * @param array $payload
   */
  public function traslado(array $payload): self
  {
    $traslado = $payload;
    $this->setTraslado($traslado);
    $this->concepto['cfdi:Impuestos'] = $this->impuesto;
    return $this;
  }

  /**
   * retencion
   *
   * @param array $payload
   */
  public function retencion(array $payload): self
  {
    $retencion = $payload;
    $this->setRetencion($retencion);
    $this->concepto['cfdi:Impuestos'] = $this->impuesto;
    return $this;
  }

  /**
   * getConcept
   */
  public function getConcept(): array
  {
    $concept = $this->concepto;
    $this->concepto = [];
    return $concept;
  }

  /**
   * isComplement
   */
  public function isComplement(): bool
  {
    return $this->existComplemnt;
  }

  /**
   * getComplementProperties
   */
  public function getComplementProperties(): array
  {
    return $this->complementProperties;
  }
}
