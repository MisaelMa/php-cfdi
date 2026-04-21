<?php

namespace Cfdi\Transform;

class CadenaEngine
{
    public static function normalizeSpace(string $s): string
    {
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    public static function requerido(?string $value): string
    {
        return '|' . self::normalizeSpace($value ?? '');
    }

    public static function opcional(?string $value): string
    {
        if ($value === null) return '';
        return '|' . self::normalizeSpace($value);
    }

    public static function generateCadenaOriginal(string $xmlContent, TemplateRegistry $registry): string
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xmlContent, LIBXML_NOERROR | LIBXML_NOWARNING);

        $root = null;
        foreach ($doc->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $root = $child;
                break;
            }
        }
        if ($root === null) return '||||';

        $rootTemplate = self::findRootElement($doc->childNodes, $registry);
        if ($rootTemplate === null) return '|||';

        if (!self::namespacesMatch($rootTemplate, $registry)) {
            return '|||';
        }

        $result = self::processNode($rootTemplate, $registry);
        return "|{$result}||";
    }

    private static function namespacesMatch(\DOMElement $xmlElement, TemplateRegistry $registry): bool
    {
        if (empty($registry->namespaces)) return true;

        foreach ($registry->namespaces as $prefix => $xsltUri) {
            $xmlUri = $xmlElement->getAttribute("xmlns:{$prefix}");
            if (!empty($xmlUri) && $xmlUri !== $xsltUri) {
                return false;
            }
        }
        return true;
    }

    private static function findRootElement(\DOMNodeList $nodes, TemplateRegistry $registry): ?\DOMElement
    {
        foreach ($nodes as $node) {
            if ($node instanceof \DOMElement) {
                if (isset($registry->templates[$node->nodeName])) {
                    return $node;
                }
                if ($node->childNodes->length > 0) {
                    $found = self::findRootElement($node->childNodes, $registry);
                    if ($found !== null) return $found;
                }
            }
        }
        return null;
    }

    public static function processNode(\DOMElement $node, TemplateRegistry $registry): string
    {
        $template = $registry->templates[$node->nodeName] ?? null;
        if ($template === null) return '';

        $result = '';

        foreach ($template->rules as $rule) {
            if ($rule instanceof AttrRule) {
                $result .= self::processAttrRule($node, $rule);
            } elseif ($rule instanceof TextRule) {
                $result .= self::processTextRule($node, $rule);
            } elseif ($rule instanceof ChildRule) {
                $result .= self::processChildRule($node, $rule, $registry);
            }
        }

        return $result;
    }

    private static function processAttrRule(\DOMElement $node, AttrRule $rule): string
    {
        $value = $node->hasAttribute($rule->name) ? $node->getAttribute($rule->name) : null;
        return $rule->required ? self::requerido($value) : self::opcional($value);
    }

    private static function processTextRule(\DOMElement $node, TextRule $rule): string
    {
        $value = null;

        if ($rule->select === '.') {
            $value = self::getTextContent($node);
        } else {
            $elements = self::resolveSelect($node, $rule->select);
            if (!empty($elements)) {
                $value = self::getTextContent($elements[0]);
            }
        }

        return $rule->required ? self::requerido($value) : self::opcional($value);
    }

    private static function getTextContent(\DOMElement $node): ?string
    {
        $parts = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $parts[] = $child->textContent;
            }
        }
        return !empty($parts) ? implode('', $parts) : null;
    }

    private static function processChildRule(\DOMElement $node, ChildRule $rule, TemplateRegistry $registry): string
    {
        if ($rule->condition !== null) {
            $conditionElements = self::resolveSelect($node, $rule->condition);
            if (empty($conditionElements)) return '';
        }

        if ($rule->wildcard) {
            return self::processWildcard($node, $registry);
        }

        $elements = $rule->descendant
            ? self::resolveDescendant($node, self::getLastSegment($rule->select))
            : self::resolveSelect($node, $rule->select);

        $result = '';

        if ($rule->forEach || !empty($elements)) {
            foreach ($elements as $el) {
                if (!empty($rule->inline)) {
                    foreach ($rule->inline as $inlineRule) {
                        if ($inlineRule instanceof AttrRule) {
                            $result .= self::processAttrRule($el, $inlineRule);
                        } elseif ($inlineRule instanceof TextRule) {
                            $result .= self::processTextRule($el, $inlineRule);
                        }
                    }
                }
                if ($rule->applyTemplates) {
                    $result .= self::processNode($el, $registry);
                }
            }
        } elseif (!$rule->forEach && $rule->applyTemplates) {
            foreach ($elements as $el) {
                $result .= self::processNode($el, $registry);
            }
        }

        return $result;
    }

    private static function processWildcard(\DOMElement $node, TemplateRegistry $registry): string
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $result .= self::processNode($child, $registry);
            }
        }
        return $result;
    }

    /** @return \DOMElement[] */
    public static function resolveSelect(\DOMElement $node, string $selectPath): array
    {
        $cleanPath = preg_replace('/^\.\//', '', $selectPath);
        $segments = explode('/', $cleanPath);
        $current = [$node];

        foreach ($segments as $segment) {
            $next = [];
            foreach ($current as $el) {
                foreach ($el->childNodes as $child) {
                    if ($child instanceof \DOMElement && $child->nodeName === $segment) {
                        $next[] = $child;
                    }
                }
            }
            $current = $next;
        }

        return $current;
    }

    /** @return \DOMElement[] */
    private static function resolveDescendant(\DOMElement $node, string $elementName): array
    {
        $results = [];
        self::collectDescendants($node, $elementName, $results);
        return $results;
    }

    private static function collectDescendants(\DOMElement $node, string $elementName, array &$results): void
    {
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                if ($child->nodeName === $elementName) {
                    $results[] = $child;
                }
                self::collectDescendants($child, $elementName, $results);
            }
        }
    }

    private static function getLastSegment(string $path): string
    {
        $segments = explode('/', $path);
        return end($segments);
    }
}
