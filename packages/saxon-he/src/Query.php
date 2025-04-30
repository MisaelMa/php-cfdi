<?php

use Cli\SaxonHe\Share;

/**
 *
 */
class Query extends Share
{
  public string $commandline = '';

  public array $commandlineArray = [];

  public string $saxonBin = '';

  /**
   *constructor
   */
  public function __construct(array $options = [])
  {
    parent::__construct();
    $binary = $options['binary'] ?? null;
    $this->saxonBin = $binary ?? $this->getOS();
    $this->commandline = $this->saxonBin;
  }

  /**
   *backup
   *
   * @param options
   * 'on' | 'off
   */
  public function backup(string $options): self
  {
    $this->commandline .= " -a:{$options}";
    $this->commandlineArray[] = "-a:{$options}";
    return $this;
  }

  /**
   *config
   *
   * @param filenames
   * string
   */
  public function config(string $filenames): self
  {
    $this->commandline .= " -config:{$filenames}";
    $this->commandlineArray[] = "-config:{$filenames}";
    return $this;
  }

  /**
   *mr
   *
   * @param classname
   * string
   */
  public function mr(string $classname): self
  {
    $this->commandline .= " -mr:{$classname}";
    $this->commandlineArray[] = "-mr:{$classname}";
    return $this;
  }

  /**
   *projection
   *
   * @param options
   * 'on' | 'off'
   */
  public function projection(string $options): self
  {
    $this->commandline .= " -projection:{$options}";
    $this->commandlineArray[] = "-projection:{$options}";
    return $this;
  }

  /**
   *queryfile
   *
   * @param queryfile
   * q
   */
  public function q(string $queryfile): self
  {
    $this->commandline .= " -q:{$queryfile}";
    $this->commandlineArray[] = "-q:{$queryfile}";
    return $this;
  }

  /**
   *qs
   *
   * @param querystring
   * string
   */
  public function qs(string $querystring): self
  {
    $this->commandline .= " -qs:{$querystring}";
    $this->commandlineArray[] = "-qs:{$querystring}";
    return $this;
  }

  /**
   *stream
   *
   * @param options
   * 'on' | 'off'
   */
  public function stream(string $options): self
  {
    $this->commandline .= " -stream:{$options}";
    $this->commandlineArray[] = "-stream:{$options}";
    return $this;
  }

  /**
   *update
   *
   * @param options
   * 'on' | 'off' | 'discard'
   */
  public function update(string $options): self
  {
    $this->commandline .= " -update:{$options}";
    $this->commandlineArray[] = "-update:{$options}";
    return $this;
  }

  /**
   *wrap
   */
  public function wrap(): self
  {
    $this->commandline .= " -wrap";
    $this->commandlineArray[] = "-wrap";
    return $this;
  }

  /**
   *getOS
   */
  private function getOS(): string
  {
    return 'query';
  }
}
