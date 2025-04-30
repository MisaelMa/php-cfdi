<?php

namespace Cli\Openssl;

/**
 *getOsComandBin
 */

class Utils
{



  static function getOsComandBin(): string
  {
    $os = PHP_OS_FAMILY;

    if ($os === 'Windows') {
      return 'openssl.exe';
    }
    if ($os === 'Linux') {
      return 'openssl';
    }
    if ($os === 'Darwin') {
      return 'openssl';
    }
    return 'openssl';
  }

  /**
   *readFileSync
   *
   * @param file
   * file
   */
  static function readFileSync(string $file): string
  {
    return file_get_contents($file);
  }
}
