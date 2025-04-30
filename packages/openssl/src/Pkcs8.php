<?php

namespace Openssl;

use Cli\Openssl\Utils;
use Cli\Openssl\Share;

/**
 *
 */
class Pkcs8 extends Share
{
  public array $commandline = [];

  public string $command = 'pkcs8';

  public string $opensslBin = '';

  /**
   *constructor
   */
  public function __construct()
  {
    parent::__construct();
    $this->opensslBin = Utils::getOsComandBin();
    $this->commandline[] = $this->opensslBin;
    $this->commandline[] = $this->command;
  }

  /**
   *topk8
   */
  public function topk8(): self
  {
    $this->commandline[] = '-topk8';
    return $this;
  }

  /**
   *traditional
   */
  public function traditional(): self
  {
    $this->commandline[] = '-traditional';
    return $this;
  }

  /**
   *iter
   *
   * @param int $count
   * count
   */
  public function iter(int $count): self
  {
    $this->commandline[] = "-iter {$count}";
    return $this;
  }

  /**
   *nocrypt
   */
  public function nocrypt(): self
  {
    $this->commandline[] = '-nocrypt';
    return $this;
  }

  /**
   *rand
   *
   * @param string $file
   * file
   */
  public function rand(string $file): self
  {
    $this->commandline[] = "-rand {$file}";
    return $this;
  }
}

$pkcs8 = new Pkcs8();
