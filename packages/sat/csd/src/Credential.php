<?php

namespace Sat\Csd;

use RuntimeException;

class Credential
{
  private Certificate $certificate;
  private PrivateKey $privateKey;

  private function __construct(Certificate $certificate, PrivateKey $privateKey)
  {
    $this->certificate = $certificate;
    $this->privateKey = $privateKey;
  }

  public static function create(string $cerPath, string $keyPath, string $password): self
  {
    $cert = Certificate::fromFile($cerPath);
    $key = PrivateKey::fromFile($keyPath, $password);
    return new self($cert, $key);
  }

  public static function fromPem(string $cerPem, string $keyPem): self
  {
    $cert = Certificate::fromPem($cerPem);
    $key = PrivateKey::fromPem($keyPem);
    return new self($cert, $key);
  }

  public function isFiel(): bool
  {
    return $this->certificate->isFiel();
  }

  public function isCsd(): bool
  {
    return $this->certificate->isCsd();
  }

  public function rfc(): string
  {
    return $this->certificate->rfc();
  }

  public function legalName(): string
  {
    return $this->certificate->legalName();
  }

  public function serialNumber(): string
  {
    return $this->certificate->serialNumber();
  }

  public function noCertificado(): string
  {
    return $this->certificate->noCertificado();
  }

  public function sign(string $data, string $algorithm = 'SHA256'): string
  {
    return $this->privateKey->sign($data, $algorithm);
  }

  public function verify(string $data, string $signature, string $algorithm = 'SHA256'): bool
  {
    try {
      $algMap = [
        'SHA256' => OPENSSL_ALGO_SHA256,
        'SHA384' => OPENSSL_ALGO_SHA384,
        'SHA512' => OPENSSL_ALGO_SHA512,
        'SHA1'   => OPENSSL_ALGO_SHA1,
      ];

      $algo = $algMap[strtoupper($algorithm)] ?? OPENSSL_ALGO_SHA256;
      $pubKeyPem = $this->certificate->publicKey();
      $pubKey = openssl_pkey_get_public($pubKeyPem);

      if ($pubKey === false) {
        return false;
      }

      $sigBinary = base64_decode($signature, true);
      if ($sigBinary === false) {
        return false;
      }

      $result = openssl_verify($data, $sigBinary, $pubKey, $algo);
      return $result === 1;
    } catch (\Throwable) {
      return false;
    }
  }

  public function isValid(): bool
  {
    return !$this->certificate->isExpired();
  }

  public function belongsTo(string $rfc): bool
  {
    return strtoupper($this->certificate->rfc()) === strtoupper($rfc);
  }

  public function keyMatchesCertificate(): bool
  {
    return $this->privateKey->belongsToCertificate($this->certificate);
  }

  public function getCertificate(): Certificate
  {
    return $this->certificate;
  }

  public function getPrivateKey(): PrivateKey
  {
    return $this->privateKey;
  }
}
