<?php

declare(strict_types=1);

namespace Cfdi\Xsd;

use Closure;
use JsonException;
use RuntimeException;

class Schema
{
    private static ?self $instance = null;

    private string $basePath = '';

    /** @var array<string, mixed> */
    private array $manifest = [];

    /** @var array<string, array> */
    private array $resolved = [];

    public static function of(): self
    {
        return self::$instance ??= new self();
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * @param array{path: string, debug?: bool} $options
     */
    public function setConfig(array $options): void
    {
        $this->basePath = rtrim($options['path'], '/');
        $this->resolved = [];
        $this->loadManifest();
    }

    /**
     * @return array<string, mixed>
     */
    public function getManifest(): array
    {
        return $this->manifest;
    }

    public function getSchema(string $key): ?array
    {
        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }
        $entry = $this->findManifestEntry($key);
        if ($entry === null) {
            return null;
        }
        $file = $this->basePath . '/' . $entry['path'] . '/' . $entry['name'] . '.json';
        if (! is_readable($file)) {
            return null;
        }
        try {
            $data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
        if (! is_array($data)) {
            return null;
        }
        $this->resolved[$key] = $data;

        return $data;
    }

    public function hasSchema(string $key): bool
    {
        return $this->getSchema($key) !== null;
    }

    /**
     * @return Closure(array): bool|null
     */
    public function validatorFor(string $key): ?Closure
    {
        if ($this->findManifestEntry($key) === null) {
            return null;
        }

        return fn (array $data): bool => $this->validateAgainstLoadedSchema($key, $data);
    }

    /**
     * @return array<string, Closure(array): bool>
     */
    public function validators(): array
    {
        $out = [];
        foreach ($this->allManifestEntries() as $entry) {
            $k = (string) $entry['key'];
            $cb = $this->validatorFor($k);
            if ($cb !== null) {
                $out[$k] = $cb;
            }
        }

        return $out;
    }

    private function loadManifest(): void
    {
        $file = $this->basePath . '/cfdi.json';
        if (! is_readable($file)) {
            throw new RuntimeException('cfdi.json not found or not readable: ' . $file);
        }
        try {
            $raw = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Invalid cfdi.json: ' . $e->getMessage(), 0, $e);
        }
        if (! is_array($raw)) {
            throw new RuntimeException('cfdi.json must decode to an object');
        }
        $this->manifest = $raw;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function allManifestEntries(): array
    {
        $blocks = [];
        foreach (['catalogos', 'comprobante', 'complementos'] as $section) {
            if (isset($this->manifest[$section]) && is_array($this->manifest[$section])) {
                foreach ($this->manifest[$section] as $row) {
                    if (is_array($row)) {
                        $blocks[] = $row;
                    }
                }
            }
        }

        return $blocks;
    }

    /**
     * @return ?array<string, mixed>
     */
    private function findManifestEntry(string $key): ?array
    {
        foreach ($this->allManifestEntries() as $row) {
            if (($row['key'] ?? null) === $key) {
                return $row;
            }
        }

        return null;
    }

    private function validateAgainstLoadedSchema(string $key, array $data): bool
    {
        $schema = $this->getSchema($key);
        if ($schema === null) {
            return false;
        }
        if ($key === 'COMPROBANTE_CONCEPTOS_CONCEPTO_PARTE_INFORMACIONADUANERA') {
            return false;
        }
        if (isset($schema['required']) && is_array($schema['required'])) {
            foreach ($schema['required'] as $field) {
                if (! is_string($field) || ! array_key_exists($field, $data)) {
                    return false;
                }
            }
        }

        return true;
    }
}
