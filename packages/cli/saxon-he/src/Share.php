<?php

namespace Cli\SaxonHe;

use Symfony\Component\Process\Process;

/**
 *
 */
class Share
{
  public string $commandline = '';

  public array $commandlineArray = [];

  public string $saxonBin = '';

  /**
   *constructor
   */
  public function __construct()
  {
    $this->commandline = $this->saxonBin;
  }

  /**
   *catalog
   *
   * @param string $filenames
   * @return $this
   */
  public function catalog(string $filenames): self
  {
    $this->commandline .= " -catalog:{$filenames}";
    $this->commandlineArray[] = "-catalog:{$filenames}";
    return $this;
  }

  /**
   *dtd
   *
   * @param string $options
   * @return $this
   */
  public function dtd(string $options): self
  {
    $this->commandline .= " -dtd:{$options}";
    $this->commandlineArray[] = "-dtd:{$options}";
    return $this;
  }

  /**
   *expand
   *
   * @param string $options
   * @return $this
   */
  public function expand(string $options): self
  {
    $this->commandline .= " -expand:{$options}";
    $this->commandlineArray[] = "-expand:{$options}";
    return $this;
  }

  /**
   *ext
   *
   * @param string $options
   * @return $this
   */
  public function ext(string $options): self
  {
    $this->commandline .= " -ext:{$options}";
    $this->commandlineArray[] = "-ext:{$options}";
    return $this;
  }

  /**
   *init
   *
   * @param string $initializer
   * @return $this
   */
  public function init(string $initializer): self
  {
    $this->commandline .= " -init:{$initializer}";
    $this->commandlineArray[] = "-init:{$initializer}";
    return $this;
  }

  /**
   * L
   *
   * @param string $options
   * @return $this
   */
  public function l(string $options): self
  {
    $this->commandline .= " -l:{$options}";
    $this->commandlineArray[] = "-l:{$options}";
    return $this;
  }

  /**
   *now
   *
   * @param string $format
   * @return $this
   */
  public function now(string $format): self
  {
    $this->commandline .= " -now:{$format}";
    $this->commandlineArray[] = "-now:{$format}";
    return $this;
  }

  /**
   * o
   *
   * @param string $filename
   * @return $this
   */
  public function o(string $filename): self
  {
    $this->commandline .= " -o:{$filename}";
    $this->commandlineArray[] = "-o:{$filename}";
    return $this;
  }

  /**
   *opt
   *
   * @param string $flags
   * @return $this
   */
  public function opt(string $flags): self
  {
    $this->commandline .= " -opt:-{$flags}";
    $this->commandlineArray[] = "-opt:-{$flags}";
    return $this;
  }

  /**
   *outval
   *
   * @param string $options
   * @return $this
   */
  public function outval(string $options): self
  {
    $this->commandline .= " -outval:{$options}";
    $this->commandlineArray[] = "-outval:{$options}";
    return $this;
  }

  /**
   *p
   *
   * @param string $options
   * @return $this
   */
  public function p(string $options): self
  {
    $this->commandline .= " -p:{$options}";
    $this->commandlineArray[] = "-p:{$options}";
    return $this;
  }

  /**
   *quit
   *
   * @param string $options
   * @return $this
   */
  public function quit(string $options): self
  {
    $this->commandline .= " -quit:{$options}";
    $this->commandlineArray[] = "-quit:{$options}";
    return $this;
  }

  /**
   *r
   *
   * @param string $classname
   * @return $this
   */
  public function r(string $classname): self
  {
    $this->commandline .= " -r:{$classname}";
    $this->commandlineArray[] = "-r:{$classname}";
    return $this;
  }

  /**
   *repeat
   *
   * @param int $integer
   * @return $this
   */
  public function repeat(int $integer): self
  {
    $this->commandline .= " -repeat:{$integer}";
    $this->commandlineArray[] = "-repeat:{$integer}";
    return $this;
  }

  /**
   *s
   *
   * @param string $filename
   * @return $this
   */
  public function s(string $filename): self
  {
    if (!file_exists($filename)) {
      throw new \RuntimeException('No se puede encontrar el xml processar. => ' . $filename);
    }
    $this->commandline .= " -s:{$filename}";
    $this->commandlineArray[] = "-s:{$filename}";
    return $this;
  }

  /**
   *sa
   *
   * @return $this
   */
  public function sa(): self
  {
    $this->commandline .= " -sa";
    $this->commandlineArray[] = "-sa";
    return $this;
  }

  /**
   *scmin
   *
   * @param string $filename
   * @return $this
   */
  public function scmin(string $filename): self
  {
    $this->commandline .= " -scmin:{$filename}";
    $this->commandlineArray[] = "-scmin:{$filename}";
    return $this;
  }

  /**
   *strip
   *
   * @param string $options
   * @return $this
   */
  public function strip(string $options): self
  {
    $this->commandline .= " -relocate:{$options}";
    $this->commandlineArray[] = "-relocate:{$options}";
    return $this;
  }

  /**
   *t
   *
   * @return $this
   */
  public function t(): self
  {
    $this->commandline .= " -t";
    $this->commandlineArray[] = "-t";
    return $this;
  }

  /**
   * T
   *
   * @param string $classname
   * @return $this
   */
  public function _T_(string $classname): self
  {
    $this->commandline .= " -T:{$classname}";
    $this->commandlineArray[] = "-T:{$classname}";
    return $this;
  }

  /**
   *TB
   *
   * @param string $filename
   * @return $this
   */
  public function TB(string $filename): self
  {
    $this->commandline .= " -TB:{$filename}";
    $this->commandlineArray[] = "-TB:{$filename}";
    return $this;
  }

  /**
   *TJ
   *
   * @return $this
   */
  public function TJ(): self
  {
    $this->commandline .= " -TJ";
    $this->commandlineArray[] = "-TJ";
    return $this;
  }

  /**
   *Tlevel
   *
   * @param string $level
   * @return $this
   */
  public function Tlevel(string $level): self
  {
    $this->commandline .= " -Tlevel:{$level}";
    $this->commandlineArray[] = "-Tlevel:{$level}";
    return $this;
  }

  /**
   *Tout
   *
   * @param string $filename
   * @return $this
   */
  public function Tout(string $filename): self
  {
    $this->commandline .= " -Tout:{$filename}";
    $this->commandlineArray[] = "-Tout:{$filename}";
    return $this;
  }

  /**
   *TP
   *
   * @param string $filename
   * @return $this
   */
  public function TP(string $filename): self
  {
    $this->commandline .= " -TP:{$filename}";
    $this->commandlineArray[] = "-TP:{$filename}";
    return $this;
  }

  /**
   *traceout
   *
   * @param string $filename
   * @return $this
   */
  public function traceout(string $filename): self
  {
    $this->commandline .= " -traceout:{$filename}";
    $this->commandlineArray[] = "-traceout:{$filename}";
    return $this;
  }

  /**
   *tree
   *
   * @param string $level
   * @return $this
   */
  public function tree(string $level): self
  {
    $this->commandline .= " -tree:{$level}";
    $this->commandlineArray[] = "-tree:{$level}";
    return $this;
  }

  /**
   *u
   *
   * @return $this
   */
  public function u(): self
  {
    $this->commandline .= " -u";
    $this->commandlineArray[] = "-u";
    return $this;
  }

  /**
   *val
   *
   * @param string $validation
   * @return $this
   */
  public function val(string $validation): self
  {
    $this->commandline .= " -val:{$validation}";
    $this->commandlineArray[] = "-val:{$validation}";
    return $this;
  }

  /**
   *x
   *
   * @param string $classname
   * @return $this
   */
  public function x(string $classname): self
  {
    $this->commandline .= " -x:{$classname}";
    $this->commandlineArray[] = "-x:{$classname}";
    return $this;
  }

  /**
   *xi
   *
   * @param string $options
   * @return $this
   */
  public function xi(string $options): self
  {
    $this->commandline .= " -xi:{$options}";
    $this->commandlineArray[] = "-xi:{$options}";
    return $this;
  }

  /**
   *xmlversion
   *
   * @param string $options
   * @return $this
   */
  public function xmlversion(string $options): self
  {
    $this->commandline .= " -xmlversion:{$options}";
    $this->commandlineArray[] = "-xmlversion:{$options}";
    return $this;
  }

  /**
   *xsd
   *
   * @param string $file
   * @return $this
   */
  public function xsd(string $file): self
  {
    $this->commandline .= " -xsd:{$file}";
    $this->commandlineArray[] = "-xsd:{$file}";
    return $this;
  }

  /**
   *xsdversion
   *
   * @param string $options
   * @return $this
   */
  public function xsdversion(string $options): self
  {
    $this->commandline .= " -xsdversion:{$options}";
    $this->commandlineArray[] = "-xsdversion:{$options}";
    return $this;
  }

  /**
   *xsiloc
   *
   * @param string $options
   * @return $this
   */
  public function xsiloc(string $options): self
  {
    $this->commandline .= " -xsiloc:{$options}";
    $this->commandlineArray[] = "-xsiloc:{$options}";
    return $this;
  }

  /**
   *feature
   *
   * @param string $value
   * @return $this
   */
  public function feature(string $value): self
  {
    $this->commandline .= " --feature:{$value}";
    $this->commandlineArray[] = "--feature:{$value}";
    return $this;
  }

  /**
   *run
   *
   * @return string
   */
  public function run(): string
  {
    try {
      $process = Process::fromShellCommandline($this->commandline);
      $process->run();

      if (!$process->isSuccessful()) {
        throw new \RuntimeException($process->getErrorOutput());
      }

      return $process->getOutput();
    } catch (\Exception $e) {
      throw new \RuntimeException('CLI Saxon Error =>' . $this->commandline);
    }
  }
}
