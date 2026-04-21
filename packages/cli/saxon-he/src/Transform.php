<?php

namespace Cli\SaxonHe;

class Transform extends Share
{
  public string $commandline = '';

  public array $commandlineArray = [];

  public string $saxonBin = '';

  public function __construct(?array $options = null)
  {
    parent::__construct();
    $binary = $options['binary'] ?? null;
    $this->saxonBin = $binary ?? $this->getOS();
    $this->commandline = $this->saxonBin;
  }

  public function a(string $options): self
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -a:{$options}";
    $this->commandlineArray[] = "-a:{$options}";
    return $this;
  }

  public function ea(string $options): self
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -ea:{$options}";
    $this->commandlineArray[] = "-ea:{$options}";
    return $this;
  }

  public function explain(string $filename): self
  {
    $this->commandline .= " -explain:{$filename}";
    $this->commandlineArray[] = "-explain:{$filename}";
    return $this;
  }

  public function exportFile(string $filename): self
  {
    $this->commandline .= " -export:{$filename}";
    $this->commandlineArray[] = "-export:{$filename}";
    return $this;
  }

  public function im(string $modename): self
  {
    $this->commandline .= " -im:{$modename}";
    $this->commandlineArray[] = "-im:{$modename}";
    return $this;
  }

  public function it(string $template): self
  {
    $this->commandline .= " -it:{$template}";
    $this->commandlineArray[] = "-it:{$template}";
    return $this;
  }

  public function jit(string $options): self
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -jit:{$options}";
    $this->commandlineArray[] = "-jit:{$options}";
    return $this;
  }

  public function lib(string $filenames): self
  {
    $this->commandline .= " -lib:{$filenames}";
    $this->commandlineArray[] = "-lib:{$filenames}";
    return $this;
  }

  public function license(string $options): self
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -license:{$options}";
    $this->commandlineArray[] = "-license:{$options}";
    return $this;
  }

  public function m(string $classname): self
  {
    $this->commandline .= " -m:{$classname}";
    $this->commandlineArray[] = "-m:{$classname}";
    return $this;
  }

  public function nogo(): self
  {
    $this->commandline .= " -nogo";
    $this->commandlineArray[] = "-nogo";
    return $this;
  }

  public function ns(string $options): self
  {
    if (!in_array($options, ['uri', '##any', '##html5'])) {
      throw new \InvalidArgumentException('Invalid ns option');
    }
    $this->commandline .= " -ns:{$options}";
    $this->commandlineArray[] = "-ns:{$options}";
    return $this;
  }

  public function orOutput(string $classname): self
  {
    $this->commandline .= " -or:{$classname}";
    $this->commandlineArray[] = "-or:{$classname}";
    return $this;
  }

  public function relocate(string $options): self
  {
    if (!in_array($options, ['on', 'off'])) {
      throw new \InvalidArgumentException('Options must be "on" or "off"');
    }
    $this->commandline .= " -relocate:{$options}";
    $this->commandlineArray[] = "-relocate:{$options}";
    return $this;
  }

  public function target(string $target): self
  {
    if (!in_array($target, ['EE', 'PE', 'HE', 'JS'])) {
      throw new \InvalidArgumentException('Invalid target option');
    }
    $this->commandline .= " -target:{$target}";
    $this->commandlineArray[] = "-target:{$target}";
    return $this;
  }

  public function threads(int $N): self
  {
    $this->commandline .= " -threads:{$N}";
    $this->commandlineArray[] = "-threads:{$N}";
    return $this;
  }

  public function warnings(string $validation): self
  {
    if (!in_array($validation, ['silent', 'recover', 'fatal'])) {
      throw new \InvalidArgumentException('Invalid warnings option');
    }
    $this->commandline .= " -warnings:{$validation}";
    $this->commandlineArray[] = "-warnings:{$validation}";
    return $this;
  }

  public function xsl(string $filename): self
  {
    if (!file_exists($filename)) {
      throw new \RuntimeException(
        'No se puede encontrar el archivo para la cadena original!.'
      );
    }
    $this->commandline .= " -xsl:{$filename}";
    $this->commandlineArray[] = "-xsl:{$filename}";
    return $this;
  }

  public function y(string $filename): self
  {
    $this->commandline .= " -y:{$filename}";
    $this->commandlineArray[] = "-y:{$filename}";
    return $this;
  }

  public function params(): self
  {
    // todo
    return $this;
  }

  private function getOS(): string
  {
    return 'transform';
  }
}
