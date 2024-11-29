<?php

namespace Sat\Cfdi;

/* use App\Types\XmlImpuestos;
use App\Types\XmlImpuestosTrasladados;
use App\Types\XmlRetencionAttributes;
use App\Types\XmlRetenciones;
use App\Types\XmlTranRentAttributesProperties;
use App\Types\XmlTranslado;
use App\Types\XmlTransladoAttributes;
use App\Utils\NumberUtils;
use App\Utils\Map;
use App\Schema\Schema; */

use Sat\Types\XmlImpuestos;
use Sat\Utils\Map;

/**
 * Class BaseImpuestos
 */
class BaseImpuestos
{
  public $impuesto = [
    '_attributes' => [],
    'cfdi:Retenciones' => [
      'cfdi:Retencion' => [],
    ],
    'cfdi:Traslados' => [
      'cfdi:Traslado' => [],
    ],
  ];

  /**
   * Constructor
   *
   * @param XmlImpuestosTrasladados|null $totalImpuestos
   */
  public function __construct(array $totalImpuestos = [])
  {
    if ($totalImpuestos !== null && count((array)$totalImpuestos) > 0) {
      $sortTotalImpuestos = Map::sortObject((array)$totalImpuestos, ['TotalImpuestosTrasladados', 'TotalImpuestosRetenidos']);
      $this->impuesto['_attributes'] = $sortTotalImpuestos;
    }
  }

  /**
   * Set Traslado
   *
   * @param array $traslado
   * @return $this
   */
  public function setTraslado(array $traslado): self
  {
    if (!isset($this->impuesto->{'cfdi:Traslados'})) {
      $this->impuesto['cfdi:Traslados'] = [
        'cfdi:Traslado' => [],
      ];
    }

    $sortTraslado = Map::sortObject($traslado, ['Base', 'Impuesto', 'TipoFactor', 'TasaOCuota', 'Importe']);

    $attributes = [
      '_attributes' => $sortTraslado,
    ];

    $this->impuesto['cfdi:Traslados']['cfdi:Traslado'][] = $attributes;
    return $this;
  }

  /**
   * Set Retencion
   *
   * @param array $retencion
   * @return $this
   */
  public function setRetencion(array $retencion): self
  {
    if (!isset($this->impuesto->{'cfdi:Retenciones'})) {
      $this->impuesto['cfdi:Retenciones'] = [
        'cfdi:Retencion' => [],
      ];
    }

    $sortRetencion = Map::sortObject($retencion, ['Base', 'Impuesto', 'TipoFactor', 'TasaOCuota', 'Importe']);
    $attributes = ['_attributes' => $sortRetencion];

    $this->impuesto['cfdi:Retenciones']['cfdi:Retencion'][] = $attributes;
    return $this;
  }

  /**
   * Get Total Impuestos
   *
   * @return XmlImpuestosTrasladados
   */
  public function getTotalImpuestos()
  {
    return $this->impuesto['_attributes'];
  }

  /**
   * Get Retenciones
   *
   * @return array
   */
  public function getRetenciones(): array
  {
    return $this->impuesto['cfdi:Retenciones']['cfdi:Retencion'] ?? [];
  }

  /**
   * Get Traslados
   *
   * @return array
   */
  public function getTraslados(): array
  {
    return $this->impuesto['cfdi:Traslados']['cfdi:Traslado'] ?? [];
  }
}
