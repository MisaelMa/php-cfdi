<?php

namespace Cfdi\Cleaner;

class CfdiCleaner
{
    public function clean(string $xml): string
    {
        $result = $xml;
        $result = Cleaners::removeStylesheetAttributes($result);
        $result = Cleaners::removeAddenda($result);
        $result = Cleaners::removeNonSatNodes($result);
        $result = Cleaners::removeNonSatNamespaces($result);
        $result = Cleaners::removeNonSatSchemaLocations($result);
        $result = Cleaners::collapseWhitespace($result);
        return $result;
    }

    public function cleanFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }
        $xml = file_get_contents($filePath);
        return $this->clean($xml);
    }
}
