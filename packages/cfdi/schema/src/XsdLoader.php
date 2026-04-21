<?php

declare(strict_types=1);

namespace Cfdi\Schema;

use DOMDocument;
use RuntimeException;

class XsdLoader
{
    private const XS_NS = 'http://www.w3.org/2001/XMLSchema';

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function loadXsd(string $source, int $timeoutSeconds = 10): DOMDocument
    {
        if (! $this->isXsdSource($source)) {
            throw new RuntimeException('Source must be a .xsd path or URL: ' . $source);
        }

        $xml = $this->readContent($source, $timeoutSeconds);
        if (trim($xml) === '') {
            throw new RuntimeException('XSD content is empty: ' . $source);
        }

        $doc = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        try {
            if (! $doc->loadXML($xml, LIBXML_NONET)) {
                $msg = $this->formatLibxmlErrors();
                throw new RuntimeException('Invalid XML: ' . $msg);
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
        }

        $root = $doc->documentElement;
        if ($root === null || $root->localName !== 'schema' || $root->namespaceURI !== self::XS_NS) {
            throw new RuntimeException('Root must be xs:schema in namespace ' . self::XS_NS);
        }

        return $doc;
    }

    private function isXsdSource(string $source): bool
    {
        $path = parse_url($source, PHP_URL_PATH) ?? $source;

        return str_ends_with(strtolower($path), '.xsd');
    }

    private function readContent(string $source, int $timeoutSeconds): string
    {
        if ($this->isUrl($source)) {
            $ctx = stream_context_create([
                'http' => ['timeout' => $timeoutSeconds],
                'https' => ['timeout' => $timeoutSeconds],
            ]);
            $content = @file_get_contents($source, false, $ctx);
            if ($content === false) {
                throw new RuntimeException('Could not fetch XSD URL: ' . $source);
            }

            return $content;
        }

        if (! is_readable($source)) {
            throw new RuntimeException('XSD file not readable: ' . $source);
        }

        $content = file_get_contents($source);
        if ($content === false) {
            throw new RuntimeException('Could not read XSD file: ' . $source);
        }

        return $content;
    }

    private function isUrl(string $source): bool
    {
        return filter_var($source, FILTER_VALIDATE_URL) !== false
            && (str_starts_with($source, 'http://') || str_starts_with($source, 'https://'));
    }

    private function formatLibxmlErrors(): string
    {
        $errors = libxml_get_errors();
        $parts = [];
        foreach ($errors as $e) {
            $parts[] = trim($e->message);
        }

        return implode('; ', $parts) ?: 'parse error';
    }
}
