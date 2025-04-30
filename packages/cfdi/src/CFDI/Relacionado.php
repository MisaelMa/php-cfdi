<?php

namespace Sat\Cfdi;

/*
 use Cfdi\Types\XmlRelacionados;
use Cfdi\Types\XmlRelacionadosAttributes;
use Cfdi\Xsd\Schema;
 */

class Relacionado
{
  private array $relacionada = [];

  /**
   * constructor
   *
   * @param XmlRelacionadosAttributes $typeRelation
   */
  public function __construct(array $typeRelation)
  {
    $this->relacionada['_attributes'] = $typeRelation;
  }

  /**
   *addRelation
   *
   * @param string $uuid
   */
  public function addRelation(string $uuid): void
  {
    if (!isset($this->relacionada['cfdi:CfdiRelacionado'])) {
      $this->relacionada['cfdi:CfdiRelacionado'] = [];
    }
    $relation = ['UUID' => $uuid];
    $this->relacionada['cfdi:CfdiRelacionado'][] = [
      '_attributes' => $relation
    ];
  }

  /**
   *getRelation
   */
  public function getRelation(): array
  {
    return $this->relacionada;
  }

  public function toJson(): array
  {
    return $this->relacionada;
  }
}
