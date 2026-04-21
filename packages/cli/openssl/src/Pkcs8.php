<?php

namespace Cli\Openssl;

class Pkcs8 extends Share
{
  protected array $commandline = [];

  protected string $command = 'pkcs8';

  protected string $opensslBin = '';

  public function __construct()
  {
    parent::__construct();
    $this->opensslBin = Utils::getOsComandBin();
    $this->commandline[] = $this->opensslBin;
    $this->commandline[] = $this->command;
  }

  public function topk8(): self
  {
    $this->commandline[] = '-topk8';
    return $this;
  }

  public function traditional(): self
  {
    $this->commandline[] = '-traditional';
    return $this;
  }

  public function iter(int $count): self
  {
    $this->commandline[] = "-iter {$count}";
    return $this;
  }

  public function nocrypt(): self
  {
    $this->commandline[] = '-nocrypt';
    return $this;
  }

  public function rand(string $file): self
  {
    $this->commandline[] = "-rand {$file}";
    return $this;
  }
}
