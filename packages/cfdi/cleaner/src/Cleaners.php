<?php

namespace Cfdi\Cleaner;

class Cleaners
{
    public static function removeStylesheetAttributes(string $xml): string
    {
        return preg_replace('/<\?xml-stylesheet[^?]*\?>/i', '', $xml);
    }

    public static function removeAddenda(string $xml): string
    {
        return preg_replace('/<cfdi:Addenda[\s\S]*?<\/cfdi:Addenda>/i', '', $xml);
    }

    public static function removeNonSatNamespaces(string $xml): string
    {
        return preg_replace_callback(
            '/(<cfdi:Comprobante\b)([\s\S]*?)(\/?>)/',
            function (array $matches) {
                $cleaned = preg_replace_callback(
                    '/\s+xmlns:[a-zA-Z0-9_-]+="([^"]*)"/',
                    function (array $nsMatch) {
                        return SatNamespaces::has($nsMatch[1]) ? $nsMatch[0] : '';
                    },
                    $matches[2]
                );
                return $matches[1] . $cleaned . $matches[3];
            },
            $xml
        );
    }

    public static function removeNonSatSchemaLocations(string $xml): string
    {
        return preg_replace_callback(
            '/xsi:schemaLocation="([^"]*)"/',
            function (array $matches) {
                $tokens = preg_split('/\s+/', trim($matches[1]));
                $kept = [];

                for ($i = 0; $i < count($tokens) - 1; $i += 2) {
                    $namespaceUri = $tokens[$i];
                    $xsdUri = $tokens[$i + 1];
                    if (SatNamespaces::has($namespaceUri)) {
                        $kept[] = $namespaceUri;
                        $kept[] = $xsdUri;
                    }
                }

                return 'xsi:schemaLocation="' . implode(' ', $kept) . '"';
            },
            $xml
        );
    }

    public static function removeNonSatNodes(string $xml): string
    {
        $prefixToUri = [];
        if (preg_match_all('/xmlns:([a-zA-Z0-9_-]+)="([^"]*)"/', $xml, $nsMatches, PREG_SET_ORDER)) {
            foreach ($nsMatches as $m) {
                $prefixToUri[$m[1]] = $m[2];
            }
        }

        return preg_replace_callback(
            '/(<cfdi:Complemento[^>]*>)([\s\S]*?)(<\/cfdi:Complemento>)/',
            function (array $matches) use ($prefixToUri) {
                $cleaned = preg_replace_callback(
                    '/<([a-zA-Z0-9_-]+):([a-zA-Z0-9_-]+)([\s\S]*?)(?:<\/\1:\2>|\/>)/',
                    function (array $nodeMatch) use ($prefixToUri) {
                        $prefix = $nodeMatch[1];
                        $uri = $prefixToUri[$prefix] ?? null;
                        if ($uri === null || !SatNamespaces::has($uri)) {
                            return '';
                        }
                        return $nodeMatch[0];
                    },
                    $matches[2]
                );
                return $matches[1] . $cleaned . $matches[3];
            },
            $xml
        );
    }

    public static function collapseWhitespace(string $xml): string
    {
        $result = preg_replace('/>[ \t\r\n]+</', ">\n<", $xml);
        return trim($result);
    }
}
