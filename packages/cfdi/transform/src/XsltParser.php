<?php

namespace Cfdi\Transform;

class XsltParser
{
    private const NAMED_TEMPLATES = ['Requerido', 'Opcional', 'ManejaEspacios'];

    public static function parse(string $mainXsltPath): TemplateRegistry
    {
        $templates = [];
        $namespaces = [];
        $visited = [];
        $stylesheetElement = null;

        self::collectFromFile($mainXsltPath, $visited, $templates, $stylesheetElement, true);

        if ($stylesheetElement instanceof \DOMElement) {
            $namespaces = self::extractNamespaces($stylesheetElement);
        }

        $parsedTemplates = [];
        foreach ($templates as $el) {
            $matchAttr = $el->getAttribute('match');
            if (empty($matchAttr) || $matchAttr === '/') continue;

            $template = self::parseTemplate($el, $matchAttr);
            $parsedTemplates[$template->match] = $template;
        }

        return new TemplateRegistry($parsedTemplates, $namespaces);
    }

    private static function extractNamespaces(\DOMElement $element): array
    {
        $namespaces = [];
        $rawXml = file_get_contents($element->ownerDocument->documentURI);
        if (preg_match('/<xsl:(stylesheet|transform)\b[^>]*>/s', $rawXml, $tagMatch)) {
            $openingTag = $tagMatch[0];
            if (preg_match_all('/\bxmlns:([a-zA-Z0-9_]+)\s*=\s*"([^"]*)"/', $openingTag, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $prefix = $match[1];
                    if (in_array($prefix, ['xsl', 'xs', 'fn'], true)) continue;
                    $namespaces[$prefix] = $match[2];
                }
            }
        }
        return $namespaces;
    }

    private static function collectFromFile(
        string $filePath,
        array &$visited,
        array &$templates,
        ?\DOMElement &$stylesheetElement,
        bool $isMain = false,
    ): void {
        $resolved = realpath($filePath);
        if ($resolved === false) return;
        if (isset($visited[$resolved])) return;
        $visited[$resolved] = true;

        $doc = new \DOMDocument();
        $doc->load($resolved);

        $stylesheet = null;
        foreach ($doc->childNodes as $child) {
            if ($child instanceof \DOMElement &&
                ($child->nodeName === 'xsl:stylesheet' || $child->nodeName === 'xsl:transform')) {
                $stylesheet = $child;
                break;
            }
        }
        if ($stylesheet === null) return;

        if ($isMain) {
            $stylesheetElement = $stylesheet;
        }

        foreach ($stylesheet->childNodes as $child) {
            if (!($child instanceof \DOMElement)) continue;

            if ($child->nodeName === 'xsl:include') {
                $href = $child->getAttribute('href');
                if (!empty($href)) {
                    $includePath = dirname($resolved) . '/' . $href;
                    self::collectFromFile($includePath, $visited, $templates, $stylesheetElement, false);
                }
            } elseif ($child->nodeName === 'xsl:template') {
                $matchAttr = $child->getAttribute('match');
                $nameAttr = $child->getAttribute('name');
                if (!empty($matchAttr) && !in_array($nameAttr, self::NAMED_TEMPLATES, true)) {
                    $templates[] = $child;
                }
            }
        }
    }

    private static function parseTemplate(\DOMElement $templateEl, string $match): ParsedTemplate
    {
        $rules = [];
        self::extractRulesFromElements($templateEl, $rules);
        return new ParsedTemplate($match, $rules);
    }

    private static function extractRulesFromElements(\DOMElement $parent, array &$rules): void
    {
        foreach ($parent->childNodes as $el) {
            if (!($el instanceof \DOMElement)) continue;

            if ($el->nodeName === 'xsl:call-template') {
                $rule = self::parseCallTemplate($el);
                if ($rule !== null) $rules[] = $rule;
            } elseif ($el->nodeName === 'xsl:apply-templates') {
                $select = $el->getAttribute('select');
                if (!empty($select) && $select !== '.') {
                    $rules[] = new ChildRule(
                        type: 'child',
                        select: self::normalizeSelect($select),
                        forEach: false,
                        inline: [],
                        applyTemplates: true,
                    );
                }
            } elseif ($el->nodeName === 'xsl:for-each') {
                $forEachRule = self::parseForEach($el);
                if ($forEachRule !== null) $rules[] = $forEachRule;
            } elseif ($el->nodeName === 'xsl:if') {
                $test = $el->getAttribute('test');
                if (!empty($test)) {
                    $innerRules = [];
                    self::extractRulesFromElements($el, $innerRules);
                    foreach ($innerRules as $r) {
                        if ($r instanceof ChildRule) {
                            $r = new ChildRule(
                                type: $r->type,
                                select: $r->select,
                                forEach: $r->forEach,
                                inline: $r->inline,
                                applyTemplates: $r->applyTemplates,
                                condition: self::normalizeSelect($test),
                                wildcard: $r->wildcard,
                                descendant: $r->descendant,
                            );
                        }
                        $rules[] = $r;
                    }
                }
            }
        }
    }

    private static function parseCallTemplate(\DOMElement $el): AttrRule|TextRule|null
    {
        $name = $el->getAttribute('name');
        if ($name !== 'Requerido' && $name !== 'Opcional') return null;

        $withParam = null;
        foreach ($el->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->nodeName === 'xsl:with-param') {
                $withParam = $child;
                break;
            }
        }
        if ($withParam === null) return null;

        $select = $withParam->getAttribute('select');
        if (empty($select)) return null;

        $attrName = self::extractAttrName($select);
        if ($attrName !== null) {
            return new AttrRule(
                type: 'attr',
                name: $attrName,
                required: $name === 'Requerido',
            );
        }

        return new TextRule(
            type: 'text',
            select: self::normalizeSelect($select),
            required: $name === 'Requerido',
        );
    }

    private static function parseForEach(\DOMElement $el): ?ChildRule
    {
        $select = $el->getAttribute('select');
        if (empty($select)) return null;

        $isWildcard = $select === './*' || $select === '*';
        $isDescendant = str_starts_with($select, './/');
        $normalizedSelect = self::normalizeSelect($select);

        $innerRules = [];
        self::extractRulesFromElements($el, $innerRules);

        $hasApplyTemplates = false;
        foreach ($el->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->nodeName === 'xsl:apply-templates') {
                $hasApplyTemplates = true;
                break;
            }
        }

        $inlineAttrs = [];
        $innerChildren = [];
        foreach ($innerRules as $r) {
            if ($r instanceof AttrRule || $r instanceof TextRule) {
                $inlineAttrs[] = $r;
            } elseif ($r instanceof ChildRule) {
                $innerChildren[] = $r;
            }
        }

        if ($isWildcard) {
            return new ChildRule(
                type: 'child',
                select: $normalizedSelect,
                forEach: true,
                inline: [],
                applyTemplates: true,
                wildcard: true,
            );
        }

        if ($hasApplyTemplates && empty($inlineAttrs) && empty($innerChildren)) {
            return new ChildRule(
                type: 'child',
                select: $normalizedSelect,
                forEach: true,
                inline: [],
                applyTemplates: true,
                descendant: $isDescendant,
            );
        }

        if (!empty($inlineAttrs)) {
            return new ChildRule(
                type: 'child',
                select: $normalizedSelect,
                forEach: true,
                inline: $inlineAttrs,
                applyTemplates: !empty($innerChildren),
                descendant: $isDescendant,
            );
        }

        if (!empty($innerChildren)) {
            return new ChildRule(
                type: 'child',
                select: $normalizedSelect,
                forEach: true,
                inline: [],
                applyTemplates: true,
                descendant: $isDescendant,
            );
        }

        return new ChildRule(
            type: 'child',
            select: $normalizedSelect,
            forEach: true,
            inline: [],
            applyTemplates: $hasApplyTemplates,
            descendant: $isDescendant,
        );
    }

    private static function extractAttrName(string $select): ?string
    {
        if (preg_match('/\.?\/?@(.+)$/', $select, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private static function normalizeSelect(string $select): string
    {
        return preg_replace('/^\.\//', '', $select);
    }
}
