<?php

namespace Cli\SaxonHe;

class Query extends Share
{
  public string $commandline = '';

  public array $commandlineArray = [];

  public string $saxonBin = '';

  public function __construct(array $options = [])
  {
    parent::__construct();
    $binary = $options['binary'] ?? null;
    $this->saxonBin = $binary ?? $this->getOS();
    $this->commandline = $this->saxonBin;
  }

  public function backup(string $options): self
  {
    $this->commandline .= " -a:{$options}";
    $this->commandlineArray[] = "-a:{$options}";
    return $this;
  }

  public function config(string $filenames): self
  {
    $this->commandline .= " -config:{$filenames}";
    $this->commandlineArray[] = "-config:{$filenames}";
    return $this;
  }

  public function mr(string $classname): self
  {
    $this->commandline .= " -mr:{$classname}";
    $this->commandlineArray[] = "-mr:{$classname}";
    return $this;
  }

  public function projection(string $options): self
  {
    $this->commandline .= " -projection:{$options}";
    $this->commandlineArray[] = "-projection:{$options}";
    return $this;
  }

  public function q(string $queryfile): self
  {
    $this->commandline .= " -q:{$queryfile}";
    $this->commandlineArray[] = "-q:{$queryfile}";
    return $this;
  }

  public function qs(string $querystring): self
  {
    $this->commandline .= " -qs:{$querystring}";
    $this->commandlineArray[] = "-qs:{$querystring}";
    return $this;
  }

  public function stream(string $options): self
  {
    $this->commandline .= " -stream:{$options}";
    $this->commandlineArray[] = "-stream:{$options}";
    return $this;
  }

  public function update(string $options): self
  {
    $this->commandline .= " -update:{$options}";
    $this->commandlineArray[] = "-update:{$options}";
    return $this;
  }

  public function wrap(): self
  {
    $this->commandline .= " -wrap";
    $this->commandlineArray[] = "-wrap";
    return $this;
  }

  private function getOS(): string
  {
    return 'query';
  }
}
