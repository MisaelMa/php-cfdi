<?php

namespace Sat\Csd;

use RuntimeException;

class Certificate
{
  private string $pem;
  /** @var array<string, mixed> */
  private array $parsed;
  /** @var \OpenSSLCertificate */
  private \OpenSSLCertificate $resource;

  private function __construct(string $pem)
  {
    $this->pem = $pem;
    $resource = openssl_x509_read($pem);
    if ($resource === false) {
      throw new RuntimeException('No se pudo leer el certificado PEM');
    }
    $this->resource = $resource;
    $parsed = openssl_x509_parse($resource);
    if ($parsed === false) {
      throw new RuntimeException('No se pudo parsear el certificado');
    }
    $this->parsed = $parsed;
  }

  public static function fromDer(string $derContent): self
  {
    $base64 = base64_encode($derContent);
    $pem = "-----BEGIN CERTIFICATE-----\n"
      . chunk_split($base64, 64, "\n")
      . "-----END CERTIFICATE-----\n";
    return new self($pem);
  }

  public static function fromPem(string $pem): self
  {
    return new self($pem);
  }

  public static function fromFile(string $filePath): self
  {
    if (!file_exists($filePath)) {
      throw new RuntimeException("Archivo no encontrado: {$filePath}");
    }
    $content = file_get_contents($filePath);
    if ($content === false) {
      throw new RuntimeException("No se pudo leer el archivo: {$filePath}");
    }

    $isPem = str_contains(substr($content, 0, 30), '-----')
      || str_ends_with(strtolower($filePath), '.pem');

    if ($isPem) {
      return self::fromPem($content);
    }
    return self::fromDer($content);
  }

  public function toPem(): string
  {
    return $this->pem;
  }

  public function toDer(): string
  {
    $pem = $this->pem;
    $pem = preg_replace('/-----[^-]+-----/', '', $pem);
    $pem = preg_replace('/\s+/', '', $pem);
    return base64_decode($pem);
  }

  public function serialNumber(): string
  {
    return $this->parsed['serialNumberHex'] ?? '';
  }

  public function noCertificado(): string
  {
    $hex = $this->serialNumber();
    if (strlen($hex) % 2 !== 0) {
      return $hex;
    }
    $pairs = str_split($hex, 2);
    $allDigits = true;
    foreach ($pairs as $p) {
      $code = hexdec($p);
      if ($code < 48 || $code > 57) {
        $allDigits = false;
        break;
      }
    }
    if ($allDigits) {
      return implode('', array_map(fn($p) => chr(hexdec($p)), $pairs));
    }
    return $hex;
  }

  public function rfc(): string
  {
    $subject = $this->parsed['subject'] ?? [];

    if (isset($subject['x500UniqueIdentifier'])) {
      $raw = trim($subject['x500UniqueIdentifier']);
      $rfcPart = trim(explode('/', $raw)[0]);
      if ($rfcPart && $this->isValidRfc($rfcPart)) {
        return $rfcPart;
      }
    }

    if (isset($subject['serialNumber'])) {
      $raw = trim($subject['serialNumber']);
      $rfcPart = trim(explode('/', $raw)[0]);
      if ($rfcPart && $this->isValidRfc($rfcPart)) {
        return $rfcPart;
      }
    }

    if (isset($subject['UID'])) {
      $val = trim($subject['UID']);
      if ($this->isValidRfc($val)) {
        return $val;
      }
    }

    foreach ($subject as $value) {
      if (is_string($value)) {
        $part = trim(explode('/', $value)[0]);
        if ($this->isValidRfc($part)) {
          return $part;
        }
      }
    }

    return '';
  }

  private function isValidRfc(string $value): bool
  {
    return (bool)preg_match('/^[A-Z&Ñ]{3,4}\d{6}[A-Z\d]{3}$/i', $value);
  }

  public function legalName(): string
  {
    $subject = $this->parsed['subject'] ?? [];
    if (isset($subject['CN'])) {
      return $subject['CN'];
    }
    if (isset($subject['GN'])) {
      return $subject['GN'];
    }
    return '';
  }

  /** @return array<string, string> */
  public function issuer(): array
  {
    return $this->parsed['issuer'] ?? [];
  }

  /** @return array<string, string> */
  public function subject(): array
  {
    return $this->parsed['subject'] ?? [];
  }

  public function validFrom(): \DateTimeImmutable
  {
    $timestamp = $this->parsed['validFrom_time_t'] ?? 0;
    return (new \DateTimeImmutable())->setTimestamp($timestamp);
  }

  public function validTo(): \DateTimeImmutable
  {
    $timestamp = $this->parsed['validTo_time_t'] ?? 0;
    return (new \DateTimeImmutable())->setTimestamp($timestamp);
  }

  public function isExpired(): bool
  {
    return new \DateTimeImmutable() > $this->validTo();
  }

  public function fingerprint(): string
  {
    $der = $this->toDer();
    $hash = strtoupper(sha1($der));
    return implode(':', str_split($hash, 2));
  }

  public function fingerprintSha256(): string
  {
    $der = $this->toDer();
    return strtoupper(hash('sha256', $der));
  }

  public function publicKey(): string
  {
    $pubKeyResource = openssl_pkey_get_public($this->resource);
    if ($pubKeyResource === false) {
      throw new RuntimeException('No se pudo obtener la clave pública');
    }
    $details = openssl_pkey_get_details($pubKeyResource);
    return $details['key'];
  }

  public function isCsd(): bool
  {
    $subject = $this->parsed['subject'] ?? [];

    if (isset($subject['OU'])) {
      $ouVal = strtoupper($subject['OU']);
      if (str_contains($ouVal, 'CSD') || str_contains($ouVal, 'CFDI') || str_contains($ouVal, 'SELLO')) {
        return true;
      }
      if (str_contains($ouVal, 'FIEL') || str_contains($ouVal, 'FIRMA')) {
        return false;
      }
    }

    $extensions = $this->parsed['extensions'] ?? [];
    if (isset($extensions['keyUsage'])) {
      $keyUsage = $extensions['keyUsage'];
      $hasDigitalSignature = str_contains($keyUsage, 'Digital Signature');
      $hasNonRepudiation = str_contains($keyUsage, 'Non Repudiation');
      if ($hasDigitalSignature && !$hasNonRepudiation) {
        return true;
      }
    }

    if (isset($subject['serialNumber']) && str_contains($subject['serialNumber'], '/')) {
      return true;
    }

    return false;
  }

  public function isFiel(): bool
  {
    return !$this->isCsd();
  }
}
