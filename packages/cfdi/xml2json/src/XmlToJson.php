<?php

namespace Cfdi\Xml2Json;

class XmlToJson
{
    public static function convert(string $xmlOrPath, bool $original = false): array
    {
        $xmlString = self::resolveInput($xmlOrPath);

        $doc = new \DOMDocument();
        $doc->loadXML($xmlString, LIBXML_NOERROR | LIBXML_NOWARNING);

        $root = $doc->documentElement;
        if ($root === null) {
            return [];
        }

        $rootName = self::stripPrefix($root->nodeName, $original);
        $result = self::buildNode($root, $rootName, $original);

        return [$rootName => $result];
    }

    private static function resolveInput(string $input): string
    {
        if (str_starts_with(trim($input), '<')) {
            return $input;
        }

        if (preg_match('/[\/\\\\]|(\.\w+)$/', $input) && file_exists($input)) {
            return file_get_contents($input);
        }

        return $input;
    }

    private static function stripPrefix(string $name, bool $original): string
    {
        if ($original) {
            return $name;
        }
        $pos = strpos($name, ':');
        return $pos !== false ? substr($name, $pos + 1) : $name;
    }

    private static function getAttributes(\DOMElement $element, bool $original): array
    {
        $attrs = [];
        foreach ($element->attributes as $attr) {
            $key = $original ? $attr->nodeName : $attr->localName;
            $attrs[$key] = $attr->value;
        }
        return $attrs;
    }

    private static function getChildElements(\DOMElement $parent): array
    {
        $children = [];
        foreach ($parent->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $children[] = $node;
            }
        }
        return $children;
    }

    private static function buildNode(\DOMElement $element, string $parentName, bool $original): array
    {
        $result = self::getAttributes($element, $original);
        $children = self::getChildElements($element);

        foreach ($children as $child) {
            $childName = self::stripPrefix($child->nodeName, $original);
            $suffix = str_replace($childName, '', $parentName);
            $isPlural = in_array($suffix, ['s', 'es'], true);

            if ($isPlural) {
                $entry = self::getAttributes($child, $original);
                $grandChildren = self::getChildElements($child);
                if (!empty($grandChildren)) {
                    $nested = self::buildNode($child, $childName, $original);
                    $entry = array_merge($entry, $nested);
                }
                $result[] = $entry;
            } else {
                $grandChildren = self::getChildElements($child);
                if (empty($grandChildren)) {
                    $attrs = self::getAttributes($child, $original);
                    if (!empty($attrs)) {
                        $result[$childName] = $attrs;
                    }
                } else {
                    $nested = self::buildNode($child, $childName, $original);
                    $attrs = self::getAttributes($child, $original);
                    $merged = array_merge($attrs, $nested);

                    if (self::isSequential($merged)) {
                        $result[$childName] = $merged;
                    } else {
                        $result[$childName] = $merged;
                    }
                }
            }
        }

        return $result;
    }

    private static function isSequential(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
