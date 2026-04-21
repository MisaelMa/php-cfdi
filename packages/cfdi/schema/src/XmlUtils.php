<?php

declare(strict_types=1);

namespace Cfdi\Schema;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class XmlUtils
{
    private const XS_NS = 'http://www.w3.org/2001/XMLSchema';

    public static function xpath(DOMDocument $doc): DOMXPath
    {
        $xp = new DOMXPath($doc);
        $xp->registerNamespace('xs', self::XS_NS);

        return $xp;
    }

    /**
     * @return list<DOMElement>
     */
    public static function childElements(DOMNode $parent): array
    {
        $out = [];
        for ($n = $parent->firstChild; $n !== null; $n = $n->nextSibling) {
            if ($n instanceof DOMElement) {
                $out[] = $n;
            }
        }

        return $out;
    }

    public static function xsElementName(DOMElement $el): ?string
    {
        if ($el->namespaceURI !== self::XS_NS || $el->localName !== 'element') {
            return null;
        }
        $name = $el->getAttribute('name');

        return $name !== '' ? $name : null;
    }

    public static function removeAnnotations(DOMElement $root): void
    {
        $xp = self::xpath($root->ownerDocument);
        $nodes = $xp->query('.//xs:annotation', $root);
        if ($nodes === false) {
            return;
        }
        foreach (iterator_to_array($nodes) as $n) {
            $p = $n->parentNode;
            if ($p !== null) {
                $p->removeChild($n);
            }
        }
    }

    /**
     * @return list<array{name: string, type: ?string, minOccurs: ?string, maxOccurs: ?string}>
     */
    public static function sequenceElementsNamed(DOMElement $sequence): array
    {
        $out = [];
        foreach (self::childElements($sequence) as $child) {
            if ($child->namespaceURI !== self::XS_NS) {
                continue;
            }
            if ($child->localName === 'element') {
                $name = self::xsElementName($child);
                if ($name !== null) {
                    $out[] = [
                        'name' => $name,
                        'type' => self::attrOrNull($child, 'type'),
                        'minOccurs' => self::attrOrNull($child, 'minOccurs'),
                        'maxOccurs' => self::attrOrNull($child, 'maxOccurs'),
                    ];
                }
            }
        }

        return $out;
    }

    /**
     * @return list<array{name: string, type: ?string, minOccurs: ?string, maxOccurs: ?string}>
     */
    public static function comprobanteSequence(DOMDocument $doc): array
    {
        $xp = self::xpath($doc);
        $seq = $xp->query(
            "//xs:element[@name='Comprobante']/xs:complexType/xs:sequence"
        );
        if ($seq === false || $seq->length === 0) {
            return [];
        }
        $sequence = $seq->item(0);
        if (! $sequence instanceof DOMElement) {
            return [];
        }

        return self::sequenceElementsNamed($sequence);
    }

    private static function attrOrNull(DOMElement $el, string $name): ?string
    {
        if (! $el->hasAttribute($name)) {
            return null;
        }
        $v = $el->getAttribute($name);

        return $v !== '' ? $v : null;
    }
}
