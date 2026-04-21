<?php

namespace Cli\Openssl;

use Exception;
use Cli\Openssl\Utils;

/**
 * CliShare
 */
class Share
{
  /** @var string[] */
  protected array $commandline = [];

  protected string $command = '';

  protected string $opensslBin = '';

  /**
   * constructor
   */
  public function __construct()
  {
    $this->opensslBin = Utils::getOsComandBin();
    $this->commandline[] = $this->opensslBin;
  }

  /**
   * inform
   *
   * @param string $options
   * options
   * 'DER' | 'PEM'
   * @return $this
   */
  public function inform(string $options): self
  {
    $this->commandline[] = "-inform {$options}";
    return $this;
  }

  /**
   * outform
   *
   * @param string $options
   * options
   * @return $this
   */
  public function outform(string $options): self
  {
    $this->commandline[] = "-outform {$options}";
    return $this;
  }

  /**
   * in
   *
   * @param string $filename
   * filename
   * @return $this
   */
  public function in(string $filename): self
  {
    $this->commandline[] = "-in {$filename}";
    return $this;
  }

  // todo https://www.openssl.org/docs/man1.1.1/man1/openssl.html
  /**
   * passin
   *
   * @param string $arg
   * arg
   * @return $this
   */
  public function passin(string $arg): self
  {
    $this->commandline[] = "-passin {$arg}";
    return $this;
  }

  /**
   * passout
   *
   * @param string $arg
   * arg
   * @return $this
   */
  public function passout(string $arg): self
  {
    $this->commandline[] = "-passout {$arg}";
    return $this;
  }

  /**
   * out
   *
   * @param string $filename
   * filename
   * @return $this
   */
  public function out(string $filename): self
  {
    $this->commandline[] = "-out {$filename}";
    return $this;
  }

  /**
   * run
   *
   * @param array $options
   * options
   * @return string
   * @throws Exception
   */
  public function run(array $options = []): string
  {
    try {
      $cli = implode(' ', $this->commandline);
      $this->commandline = array_slice($this->commandline, 0, 2);
      $process = proc_open($cli, [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
      ], $pipes);

      if (!is_resource($process)) {
        throw new Exception('Failed to execute command');
      }

      $output = stream_get_contents($pipes[1]);
      fclose($pipes[1]);
      fclose($pipes[2]);
      proc_close($process);

      return $output;
    } catch (Exception $e) {
      throw new Exception('run: ' . $e->getMessage());
    }
  }

  /**
   * cli
   *
   * @return string
   * @throws Exception
   */
  public function cli(): string
  {
    try {
      $cli = implode(' ', $this->commandline);
      $this->commandline = array_slice($this->commandline, 0, 2);
      return $cli;
    } catch (Exception $e) {
      throw new Exception('cli: ' . $e->getMessage());
    }
  }
}
