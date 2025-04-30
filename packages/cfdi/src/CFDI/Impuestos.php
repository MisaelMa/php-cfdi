<?php

namespace Sat\Cfdi;

use Sat\Cfdi\BaseImpuestos;
/* use Cfdi\Types\XmlImpuestos;
use Cfdi\Types\XmlImpuestosTrasladados;
use Cfdi\Types\XmlRetencionAttributes;
use Cfdi\Types\XmlRetenciones;
use Cfdi\Types\XmlTranRentAttributesProperties;
use Cfdi\Types\XmlTranslado;
use Cfdi\Types\XmlTransladoAttributes;
use Cfdi\BaseImpuestos;
use Cfdi\Schema;
use Cfdi\Utils\NumberUtils; */

class Impuestos extends BaseImpuestos
{
  public function __construct(array $totalImpuestos = [])
  {
    parent::__construct($totalImpuestos);
  }

  public function traslados(array $payload)
  {
    $traslado = $payload;
    $this->setTraslado($traslado);
    return $this;
  }

  public function retenciones(array $payload)
  {
    $retencion = $payload;
    $this->setRetencion($retencion);
    return $this;
  }
}
