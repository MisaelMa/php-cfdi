<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

class Iedu extends Complemento
{
  private const XMLNS = 'http://www.sat.gob.mx/iedu';
  private const XSD = 'http://www.sat.gob.mx/sitio_internet/cfd/iedu/iedu.xsd';

  /**
   * @param array{version: string, nombreAlumno: string, CURP: string, nivelEducativo: string, autRVOE: string, rfcPago: string} $attributes
   */
  /**
   * @param array{version: string, nombreAlumno: string, CURP: string, nivelEducativo: string, autRVOE: string, rfcPago: string} $attributes
   */
  public function __construct(array $attributes)
  {
    parent::__construct('iedu:instEducativas', self::XMLNS, self::XSD);
    $this->complemento = [
      '_attributes' => $attributes,
    ];
  }

  public static function iedu(): string
  {
    return 'Iedu';
  }
}
