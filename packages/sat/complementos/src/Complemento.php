<?php

declare(strict_types=1);

namespace Sat\Cfdi\Complementos;

abstract class Complemento
{
  public array $complemento = [];
  private string $xmlns;
  private string $key;
  /** @var string[] */
  private array $schemaLocation = [];
  private string $xmlnskey;

  public function __construct(string $key, string $xmlns, string $xsd)
  {
    $this->xmlns = $xmlns;
    $this->key = $key;
    $this->xmlnskey = explode(':', $this->key)[0];
    $this->schemaLocation[] = $xmlns;
    $this->schemaLocation[] = $xsd;
  }

  /** @return array{complement: array, key: string, schemaLocation: string[], xmlns: string, xmlnskey: string} */
  public function getComplement(): array
  {
    return [
      'complement' => $this->complemento,
      'key' => $this->key,
      'schemaLocation' => $this->schemaLocation,
      'xmlns' => $this->xmlns,
      'xmlnskey' => $this->xmlnskey,
    ];
  }
}
