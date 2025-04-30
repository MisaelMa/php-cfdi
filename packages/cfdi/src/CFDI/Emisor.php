<?php

namespace Sat\Cfdi;

class Emisor
{
  private array $emisor = [
    '_attributes' => [
      'Rfc' => '',
      'Nombre' => '',
      'RegimenFiscal' => '',
    ]
  ];

  /**
   * constructor
   *
   * @param array $emisor
   */
  public function __construct(array $emisor)
  {
    $this->emisor['_attributes'] = $emisor;
  }

  public function setRfc(string $rfc): void
  {
    $this->emisor['_attributes']['Rfc'] = $rfc;
  }

  public function setNombre(string $nombre): void
  {
    $this->emisor['_attributes']['Nombre'] = $nombre;
  }

  public function setRegimenFiscal(string|int $regimenFiscal): void
  {
    $this->emisor['_attributes']['RegimenFiscal'] = $regimenFiscal;
  }

  public function setFacAtrAdquirente(string|int $facAtrAdquirente): void
  {
    $this->emisor['_attributes']['FacAtrAdquirente'] = $facAtrAdquirente;
  }

  /**
   * toJson
   *
   * @return array
   */
  public function toArray(): array
  {
    return $this->emisor;
  }
}
