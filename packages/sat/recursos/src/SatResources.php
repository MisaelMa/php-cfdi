<?php

declare(strict_types=1);

namespace Sat\Recursos;

final class SatResources
{
    private const SAT_URLS = [
        '4.0' => [
            'schema' => 'https://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd',
            'xslt' => 'https://www.sat.gob.mx/sitio_internet/cfd/4/cadenaoriginal_4_0/cadenaoriginal_4_0.xslt',
        ],
        '3.3' => [
            'schema' => 'https://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd',
            'xslt' => 'https://www.sat.gob.mx/sitio_internet/cfd/3/cadenaoriginal_3_3/cadenaoriginal_3_3.xslt',
        ],
    ];

    private readonly string $version;

    private readonly string $outputDir;

    public function __construct(string $version, string $outputDir)
    {
        if ($version !== '4.0' && $version !== '3.3') {
            throw new \InvalidArgumentException('version must be 3.3 or 4.0');
        }
        $this->version = $version;
        $this->outputDir = $outputDir;
    }

    /**
     * @return array{
     *   schema: string,
     *   xslt: string,
     *   catalogSchema: string|null,
     *   tipoDatosSchema: string|null,
     *   complementos: list<string>,
     *   unused: list<string>,
     *   added: list<string>,
     * }
     */
    public function download(): array
    {
        $urls = self::SAT_URLS[$this->version];
        $complementosDir = $this->outputDir . DIRECTORY_SEPARATOR . 'complementos';

        if (! is_dir($this->outputDir) && ! mkdir($this->outputDir, 0775, true) && ! is_dir($this->outputDir)) {
            throw new \RuntimeException('Could not create output directory');
        }
        if (! is_dir($complementosDir) && ! mkdir($complementosDir, 0775, true) && ! is_dir($complementosDir)) {
            throw new \RuntimeException('Could not create complementos directory');
        }

        $schemaContent = $this->fetchText($urls['schema']);
        $schemaFileName = $this->version === '4.0' ? 'cfdv40.xsd' : 'cfdv33.xsd';
        $schemaPath = $this->outputDir . DIRECTORY_SEPARATOR . $schemaFileName;
        file_put_contents($schemaPath, $schemaContent);

        $imports = $this->extractSchemaImports($schemaContent);
        $catalogUrl = $imports['catalogUrl'];
        $tipoDatosUrl = $imports['tipoDatosUrl'];

        $catalogSchemaPath = null;
        if ($catalogUrl !== null) {
            try {
                $catalogContent = $this->fetchText($catalogUrl);
                $catalogFileName = basename(parse_url($catalogUrl, PHP_URL_PATH) ?: $catalogUrl);
                $catalogFileName = explode('?', $catalogFileName, 2)[0];
                $catalogSchemaPath = $this->outputDir . DIRECTORY_SEPARATOR . $catalogFileName;
                file_put_contents($catalogSchemaPath, $catalogContent);
            } catch (\Throwable) {
                $catalogSchemaPath = null;
            }
        }

        $tipoDatosSchemaPath = null;
        if ($tipoDatosUrl !== null) {
            try {
                $tipoDatosContent = $this->fetchText($tipoDatosUrl);
                $tipoDatosFileName = basename(parse_url($tipoDatosUrl, PHP_URL_PATH) ?: $tipoDatosUrl);
                $tipoDatosFileName = explode('?', $tipoDatosFileName, 2)[0];
                $tipoDatosSchemaPath = $this->outputDir . DIRECTORY_SEPARATOR . $tipoDatosFileName;
                file_put_contents($tipoDatosSchemaPath, $tipoDatosContent);
            } catch (\Throwable) {
                $tipoDatosSchemaPath = null;
            }
        }

        $rawXslt = $this->fetchText($urls['xslt']);
        $cleanXslt = $this->cleanXml($rawXslt);
        $includeUrls = $this->extractXslIncludes($cleanXslt);

        $downloadedComplementos = [];
        foreach ($includeUrls as $includeUrl) {
            try {
                $complementoContent = $this->fetchText($includeUrl);
                $cleanComplemento = $this->cleanXml($complementoContent);
                $fileName = basename(parse_url($includeUrl, PHP_URL_PATH) ?: $includeUrl);
                $fileName = explode('?', $fileName, 2)[0];
                $complementoPath = $complementosDir . DIRECTORY_SEPARATOR . $fileName;
                file_put_contents($complementoPath, $cleanComplemento);
                $downloadedComplementos[] = $complementoPath;
            } catch (\Throwable) {
            }
        }

        $localXslt = $this->rewriteIncludes($cleanXslt, $includeUrls);
        $xsltPath = $this->outputDir . DIRECTORY_SEPARATOR . 'cadenaoriginal.xslt';
        file_put_contents($xsltPath, $localXslt);

        $downloadedFileNames = array_map('basename', $downloadedComplementos);
        $downloadedSet = array_fill_keys($downloadedFileNames, true);
        $diff = $this->diffComplementos($complementosDir, $downloadedSet);

        return [
            'schema' => $schemaPath,
            'xslt' => $xsltPath,
            'catalogSchema' => $catalogSchemaPath,
            'tipoDatosSchema' => $tipoDatosSchemaPath,
            'complementos' => $downloadedComplementos,
            'unused' => $diff['unused'],
            'added' => $diff['added'],
        ];
    }

    public function cleanXml(string $content): string
    {
        $xmlDeclarationIndex = strpos($content, '<?xml');
        $xslStylesheetIndex = strpos($content, '<xsl:stylesheet');
        $xsStylesheetIndex = strpos($content, '<xs:schema');
        $xsdSchemaIndex = strpos($content, '<schema');

        $candidates = array_filter(
            [$xmlDeclarationIndex, $xslStylesheetIndex, $xsStylesheetIndex, $xsdSchemaIndex],
            static fn ($i) => $i !== false
        );

        if ($candidates === []) {
            return $content;
        }

        $startIndex = min($candidates);
        $cleaned = substr($content, $startIndex);

        if (
            ! str_starts_with($cleaned, '<?xml')
            && (str_contains($cleaned, '<xsl:stylesheet') || str_contains($cleaned, '<xsl:transform'))
        ) {
            return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . $cleaned;
        }

        return $cleaned;
    }

    private function fetchText(string $url): string
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 120,
                'ignore_errors' => true,
            ],
        ]);

        $text = @file_get_contents($url, false, $ctx);
        if ($text === false) {
            throw new \RuntimeException("Error al descargar {$url}");
        }

        $responseHeaders = $http_response_header ?? [];
        $statusLine = $responseHeaders[0] ?? '';
        if (preg_match('#HTTP/\S+\s+(\d+)#', $statusLine, $m) && (int) $m[1] !== 200) {
            throw new \RuntimeException("Error al descargar {$url}: {$m[1]}");
        }

        return $text;
    }

    /**
     * @return array{catalogUrl: string|null, tipoDatosUrl: string|null}
     */
    private function extractSchemaImports(string $schemaContent): array
    {
        $catalogUrl = null;
        $tipoDatosUrl = null;

        if (preg_match_all('/<xs:import[^>]*schemaLocation=["\']([^"\']+)["\'][^>]*>/i', $schemaContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $schemaLocation = $match[1];
                if (str_contains($schemaLocation, 'catCFDI') || str_contains($schemaLocation, 'catalogos')) {
                    $catalogUrl = $schemaLocation;
                } elseif (str_contains($schemaLocation, 'tdCFDI') || str_contains($schemaLocation, 'tipoDatos')) {
                    $tipoDatosUrl = $schemaLocation;
                }
            }
        }

        return ['catalogUrl' => $catalogUrl, 'tipoDatosUrl' => $tipoDatosUrl];
    }

    /**
     * @return list<string>
     */
    private function extractXslIncludes(string $xsltContent): array
    {
        $urls = [];
        if (preg_match_all('/<xsl:include[^>]*href=["\']([^"\']+)["\'][^>]*\/?>/i', $xsltContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $href = $match[1];
                if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
                    $urls[] = $href;
                }
            }
        }

        return $urls;
    }

    /**
     * @param array<string, true> $downloadedFileNames
     * @return array{unused: list<string>, added: list<string>}
     */
    private function diffComplementos(string $complementosDir, array $downloadedFileNames): array
    {
        $localFiles = [];
        if (is_dir($complementosDir)) {
            foreach (scandir($complementosDir) ?: [] as $f) {
                if (str_ends_with($f, '.xslt')) {
                    $localFiles[] = $f;
                }
            }
        }

        $localSet = array_fill_keys($localFiles, true);

        $unused = [];
        foreach ($localFiles as $f) {
            if (! isset($downloadedFileNames[$f])) {
                $unused[] = $f;
            }
        }

        $added = [];
        foreach (array_keys($downloadedFileNames) as $f) {
            if (! isset($localSet[$f])) {
                $added[] = $f;
            }
        }

        return ['unused' => $unused, 'added' => $added];
    }

    /**
     * @param list<string> $includeUrls
     */
    private function rewriteIncludes(string $xsltContent, array $includeUrls): string
    {
        $result = $xsltContent;
        foreach ($includeUrls as $url) {
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $fileName = basename($path);
            $fileName = explode('?', $fileName, 2)[0];
            $localHref = './complementos/' . $fileName;
            $result = str_replace($url, $localHref, $result);
        }

        return $result;
    }
}
