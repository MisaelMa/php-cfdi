<?php

namespace Cfdi\Csd;

class Key
{
  private static bool $isKey = false;
  private static array $allowedFiles = ['.key', '.pem'];
  private static string $password = '';
  private static string $file = '';
  private static string $regex;

  public function __construct()
  {
    self::$regex = '/([a-zA-Z0-9s_\.-:])+(' . implode('|', self::$allowedFiles) . ')$/';
  }

  public static function setFile(string $keyfile, ?string $pass = null): void
  {
    if (preg_match('/\.[0-9a-z]+$/i', $keyfile, $typeFile)) {
      if (preg_match(self::$regex, strtolower($keyfile))) {
        self::$file = $keyfile;
        if (!$pass && $typeFile[0] === '.key') {
          throw new \Exception("contraseña requerida de el archivo {$keyfile}");
        }
        if ($typeFile[0] === '.key' && $pass) {
          self::$password = $pass;
          self::$isKey = true;
        }
      } else {
        error_log('files not supported');
      }
    }
  }

  public static function getPem(array $options = ['begin' => false]): string
  {
    try {
      $begin = $options['begin'] ?? false;
      $pem = '';

      if (self::$isKey) {
        // Using openssl to convert DER to PEM
        $command = sprintf(
          'openssl pkcs8 -inform DER -in %s -outform PEM -passin pass:%s',
          escapeshellarg(self::$file),
          escapeshellarg(self::$password)
        );
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
          throw new \Exception("Failed to convert key file");
        }

        $pem = implode("\n", $output);
      } else {
        $pem = file_get_contents(self::$file);
      }

      if ($begin) {
        return preg_replace(['/(-+[^-]+-+)/', '/\s+/'], '', $pem);
      }

      return $pem;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public static function getData(): mixed
  {
    return openssl_pkey_get_private(self::getPem());
  }

  public static function signatureHexForge(string $message): string
  {
    $privateKey = self::getData();
    $signature = '';

    if (!openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
      throw new \Exception("Failed to create signature");
    }

    return base64_encode($signature);
  }

  public static function signatureHexCrypto(string $message): string
  {
    $privateKey = self::getPem();
    $signature = '';

    if (!openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
      throw new \Exception("Failed to create signature");
    }

    return base64_encode($signature);
  }
}
