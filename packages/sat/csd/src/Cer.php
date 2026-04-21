<?php

namespace Sat\Csd;

use DateTime;
use Exception;
use Cli\Openssl\X509;

class Cer
{
  private bool $isCert = false;
  private array $allowedFiles = ['.cer', '.pem'];
  private string $file = '';
  private string $regex;

  public function __construct()
  {
    $this->regex = '/([a-zA-Z0-9s_\\.-:])+(' . implode('|', $this->allowedFiles) . ')$/';
  }

  public function setFile(string $filePath): void
  {
    if (preg_match('/\.[0-9a-z]+$/i', $filePath, $typeFile)) {
      if (preg_match($this->regex, strtolower($filePath))) {
        $this->file = $filePath;
        error_log('typeFile ' . $typeFile[0]);
        if ($typeFile[0] === '.cer') {
          $this->isCert = true;
        }
      } else {
        error_log('files not supported');
      }
    }
  }

  public function getPem(array $options = ['begin' => false]): string
  {
    try {
      $begin = $options['begin'] ?? false;
      $pem = '';

      if ($this->isCert) {
        $x509 = new X509();
        $pem = $x509->inform('DER')->in($this->file)->outform('PEM')->run();
      } else {
        $pem = file_get_contents($this->file);
      }

      if ($begin) {
        return preg_replace(['/(-+[^-]+-+)/', '/\s+/'], '', $pem);
      }
      return $pem;
    } catch (Exception $e) {
      throw new Exception('Failed to get PEM: ' . $e->getMessage());
    }
  }

  public function getData()
  {

    $pem  = $this->getPem();

    $certResource = openssl_x509_read($pem);
    if (!$certResource) {
      throw new Exception("No se pudo leer el certificado PEM.");
    }

    $parsed = openssl_x509_parse($certResource);
    if ($parsed === false) {
      throw new Exception("No se pudo parsear el certificado.");
    }

    // Obtener la clave pública
    $pubKeyResource = openssl_pkey_get_public($pem);
    if (!$pubKeyResource) {
      throw new Exception("No se pudo obtener la clave pública.");
    }

    $details = openssl_pkey_get_details($pubKeyResource);

    return [
      'subject' => $parsed['subject'] ?? null,
      'issuer' => $parsed['issuer'] ?? null,
      'validFrom' => $parsed['validFrom_time_t'] ?? null,
      'validTo' => $parsed['validTo_time_t'] ?? null,
      'serialNumber' => $parsed['serialNumberHex'] ?? null,
      'version' => $parsed['version'] ?? null,
      'extensions' => $parsed['extensions'] ?? null,
      'publicKey' => [
        'key' => $details['key'] ?? null,
        'type' => $details['type'] ?? null,
        'details' => $details,
      ],
      'raw' => $pem,
    ];
  }

  public function version(): int
  {
    return $this->getData()['version'];
  }

  public function getNoCer(): string
  {
    $serialNumber = $this->getData()['serialNumber']; // hex();
    $pairs = str_split($serialNumber, 2);
    return implode('', array_map(function ($v) {
      return chr(hexdec($v));
    }, $pairs));
  }

  public function text(): string
  {
    try {
      return openssl_x509_parse(file_get_contents($this->file), true);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  public function pubkey(array $options = ['begin' => false]): string
  {
    try {
      $cert = openssl_x509_read(file_get_contents($this->file));
      $pubkey = openssl_pkey_get_public($cert);
      $keyData = openssl_pkey_get_details($pubkey);
      return $keyData['key'];
    } catch (Exception $e) {
      error_log($e->getMessage());
      throw new Exception();
    }
  }

  // Additional methods would follow similar pattern of conversion
  // Converting each TypeScript/Node.js function to PHP equivalent
  // Using native PHP OpenSSL functions where possible

  public function validity(): array
  {
    $cert = openssl_x509_parse(file_get_contents($this->file));
    return [
      'notBefore' => new DateTime('@' . $cert['validFrom_time_t']),
      'notAfter' => new DateTime('@' . $cert['validTo_time_t'])
    ];
  }
}
