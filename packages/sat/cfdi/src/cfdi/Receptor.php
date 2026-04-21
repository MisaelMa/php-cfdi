<?php

namespace Sat\Cfdi;

/**
 * Class Receptor
 */
class Receptor
{
  private array $receptor = [
    '_attributes' => [
      'Rfc' => '',
      'Nombre' => '',
      'UsoCFDI' => '',
      'DomicilioFiscalReceptor' => '',
      'RegimenFiscalReceptor' => '',
    ]
  ];

  /**
   * Constructor
   * 
   * @param array $receptor
   */
  public function __construct(array $receptor)
  {
    $this->receptor['_attributes'] = $receptor;
  }

  public function setRFC(string $rfc): void
  {
    $this->receptor['_attributes']['Rfc'] = $rfc;
  }

  public function setNombre(string $nombre): void
  {
    $this->receptor['_attributes']['Nombre'] = $nombre;
  }

  public function setUsoCFDI(string $usoCFDI): void
  {
    $this->receptor['_attributes']['UsoCFDI'] = $usoCFDI;
  }

  public function setDomicilioFiscalReceptor(string $domicilioFiscalReceptor): void
  {
    $this->receptor['_attributes']['DomicilioFiscalReceptor'] = $domicilioFiscalReceptor;
  }

  public function setResidenciaFiscal(string $residenciaFiscal): void
  {
    $this->receptor['_attributes']['ResidenciaFiscal'] = $residenciaFiscal;
  }

  public function setNumRegIdTrib(string $numRegIdTrib): void
  {
    $this->receptor['_attributes']['NumRegIdTrib'] = $numRegIdTrib;
  }

  public function setRegimenFiscalReceptor(string $regimenFiscalReceptor): void
  {
    $this->receptor['_attributes']['RegimenFiscalReceptor'] = $regimenFiscalReceptor;
  }

  /**
   * toJson
   * 
   * @return array
   */
  public function toArray(): array
  {
    return $this->receptor;
  }
}
