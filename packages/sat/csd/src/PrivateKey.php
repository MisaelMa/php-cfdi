<?php

namespace Sat\Csd;

use RuntimeException;

class PrivateKey
{
  private string $pem;
  /** @var \OpenSSLAsymmetricKey */
  private \OpenSSLAsymmetricKey $keyResource;

  private function __construct(string $pem)
  {
    $this->pem = $pem;
    $key = openssl_pkey_get_private($pem);
    if ($key === false) {
      throw new RuntimeException('No se pudo cargar la llave privada PEM');
    }
    $this->keyResource = $key;
  }

  public static function fromDer(string $derContent, string $password): self
  {
    $pkeyId = openssl_pkey_get_private($derContent, $password);
    if ($pkeyId !== false) {
      openssl_pkey_export($pkeyId, $pem);
      return new self($pem);
    }

    $tmpIn = tempnam(sys_get_temp_dir(), 'csd_key_');
    $tmpOut = tempnam(sys_get_temp_dir(), 'csd_pem_');

    try {
      file_put_contents($tmpIn, $derContent);
      $cmd = sprintf(
        'openssl pkcs8 -inform DER -in %s -outform PEM -passin pass:%s -out %s 2>&1',
        escapeshellarg($tmpIn),
        escapeshellarg($password),
        escapeshellarg($tmpOut)
      );
      exec($cmd, $output, $returnCode);

      if ($returnCode !== 0) {
        throw new RuntimeException('Error al descifrar la llave privada (password incorrecto o formato invalido)');
      }

      $pem = file_get_contents($tmpOut);
      if ($pem === false || trim($pem) === '') {
        throw new RuntimeException('No se pudo leer la llave privada descifrada');
      }

      return new self($pem);
    } finally {
      @unlink($tmpIn);
      @unlink($tmpOut);
    }
  }

  public static function fromPem(string $pem): self
  {
    return new self($pem);
  }

  public static function fromFile(string $filePath, string $password = ''): self
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
    return self::fromDer($content, $password);
  }

  public function toPem(): string
  {
    return $this->pem;
  }

  public function sign(string $data, string $algorithm = 'SHA256'): string
  {
    $algMap = [
      'SHA256' => OPENSSL_ALGO_SHA256,
      'SHA384' => OPENSSL_ALGO_SHA384,
      'SHA512' => OPENSSL_ALGO_SHA512,
      'SHA1'   => OPENSSL_ALGO_SHA1,
    ];

    $algo = $algMap[strtoupper($algorithm)] ?? OPENSSL_ALGO_SHA256;
    $signature = '';

    if (!openssl_sign($data, $signature, $this->keyResource, $algo)) {
      throw new RuntimeException('Error al firmar los datos');
    }

    return base64_encode($signature);
  }

  public function belongsToCertificate(Certificate $cert): bool
  {
    try {
      $certPubKeyPem = $cert->publicKey();
      $certPubKey = openssl_pkey_get_public($certPubKeyPem);
      $privPubKey = openssl_pkey_get_details($this->keyResource);

      if ($certPubKey === false || $privPubKey === false) {
        return false;
      }

      $certDetails = openssl_pkey_get_details($certPubKey);

      return $certDetails['key'] === $privPubKey['key'];
    } catch (\Throwable) {
      return false;
    }
  }
}
