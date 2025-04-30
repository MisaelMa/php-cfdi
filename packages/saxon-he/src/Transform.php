<?php

use Cli\Openssl\Share;

/**
 *
 */
class Transform extends Share
{
  public string $commandline = '';

  public array $commandlineArray = [];

  public string $saxonBin = '';

  /**
   *constructor
   */
  public function __construct(?array $options = null)
  {
    parent::__construct();
    $binary = $options['binary'] ?? null;
    $this->saxonBin = $binary ?? $this->getOS();
    $this->commandline = $this->saxonBin;
  }

  /**
   *a
   *
   * @param options
   * options
   */
  public function a(string $options): Transform
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -a:{$options}";
    $this->commandlineArray[] = "-a:{$options}";
    return $this;
  }

  /**
   *ea
   *
   * @param options
   * options
   */
  public function ea(string $options): Transform
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -ea:{$options}";
    $this->commandlineArray[] = "-ea:{$options}";
    return $this;
  }

  /**
   *explain
   *
   * @param filename
   * string
   */
  public function explain(string $filename): Transform
  {
    $this->commandline .= " -explain:{$filename}";
    $this->commandlineArray[] = "-explain:{$filename}";
    return $this;
  }

  /**
   *export
   *
   * @param filename
   * string
   */
  public function export(string $filename): Transform
  {
    $this->commandline .= " -export:{$filename}";
    $this->commandlineArray[] = "-export:{$filename}";
    return $this;
  }

  /**
   *im
   *
   * @param modename
   * string
   */
  public function im(string $modename): Transform
  {
    $this->commandline .= " -im:{$modename}";
    $this->commandlineArray[] = "-im:{$modename}";
    return $this;
  }

  /**
   *it
   *
   * @param template
   * string
   */
  public function it(string $template): Transform
  {
    $this->commandline .= " -it:{$template}";
    $this->commandlineArray[] = "-it:{$template}";
    return $this;
  }

  /**
   *jit
   *
   * @param options
   * 'on' | 'off'
   */
  public function jit(string $options): Transform
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -jit:{$options}";
    $this->commandlineArray[] = "-jit:{$options}";
    return $this;
  }

  /**
   *lib
   *
   * @param filenames
   *string
   */
  public function lib(string $filenames): Transform
  {
    $this->commandline .= " -lib:{$filenames}";
    $this->commandlineArray[] = "-lib:{$filenames}";
    return $this;
  }

  /**
   *license
   *
   * @param options
   * options
   */
  public function license(string $options): Transform
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -license:{$options}";
    $this->commandlineArray[] = "-license:{$options}";
    return $this;
  }

  /**
   *m
   *
   * @param classname
   * string
   */
  public function m(string $classname): Transform
  {
    $this->commandline .= " -m:{$classname}";
    $this->commandlineArray[] = "-m:{$classname}";
    return $this;
  }

  /**
   *nogo
   */
  public function nogo(): Transform
  {
    $this->commandline .= " -nogo";
    $this->commandlineArray[] = "-nogo";
    return $this;
  }

  /**
   *ns
   *
   * @param options
   * 'uri' | '##any' | '##html5'
   */
  public function ns(string $options): Transform
  {
    if (!in_array($options, ['uri', '##any', '##html5'])) {
      throw new \InvalidArgumentException('Invalid ns option');
    }
    $this->commandline .= " -ns:{$options}";
    $this->commandlineArray[] = "-ns:{$options}";
    return $this;
  }

  /**
   *or
   *
   * @param classname
   * string
   */
  public function or(string $classname): Transform
  {
    $this->commandline .= " -or:{$classname}";
    $this->commandlineArray[] = "-or:{$classname}";
    return $this;
  }

  /**
   *relocate
   *
   * @param options
   * 'on' | 'off'
   */
  public function relocate(string $options): Transform
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -relocate:{$options}";
    $this->commandlineArray[] = "-relocate:{$options}";
    return $this;
  }

  /**
   *target
   *
   * @param target
   * target
   */
  public function target(string $target): Transform
  {
    if (!in_array($target, ['EE', 'PE', 'HE', 'JS'])) {
      throw new \InvalidArgumentException('Invalid target option');
    }
    $this->commandline .= " -target:{$target}";
    $this->commandlineArray[] = "-target:{$target}";
    return $this;
  }

  /**
   *threads
   *
   * @param N
   * number
   */
  public function threads(int $N): Transform
  {
    // todo only -S is activate
    $this->commandline .= " -threads:{$N}";
    $this->commandlineArray[] = "-threads:{$N}";
    return $this;
  }

  /**
   *warnings
   *
   * @param validation
   * 'silent' | 'recover' | 'fatal'
   */
  public function warnings(string $validation): Transform
  {
    if (!in_array($validation, ['silent', 'recover', 'fatal'])) {
      throw new \InvalidArgumentException('Invalid warnings option');
    }
    $this->commandline .= " -warnings:{$validation}";
    $this->commandlineArray[] = "-warnings:{$validation}";
    return $this;
  }

  /**
   * xsl
   *
   * @param filename
   * string
   */
  public function xsl(string $filename): Transform
  {
    if (!file_exists($filename)) {
      throw new \Exception(
        'No se puede encontrar el archivo para la cadena original!.'
      );
    }
    $this->commandline .= " -xsl:{$filename}";
    $this->commandlineArray[] = "-xsl:{$filename}";
    return $this;
  }

  /**
   *y
   *
   * @param filename
   * string
   */
  public function y(string $filename): Transform
  {
    $this->commandline .= " -y:{$filename}";
    $this->commandlineArray[] = "-y:{$filename}";
    return $this;
  }

  /**
   *params
   *
   */
  public function params(): Transform
  {
    // todo
    return $this;
  }

  /**
   *string
   */
  private function getOS(): string
  {
    return 'transform';
  }
}
