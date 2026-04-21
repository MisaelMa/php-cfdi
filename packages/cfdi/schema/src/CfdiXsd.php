<?php

declare(strict_types=1);

namespace Cfdi\Schema;

class CfdiXsd
{
    private static ?self $instance = null;

    private string $source = '';

    public static function of(): self
    {
        return self::$instance ??= new self();
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * @param array{source?: string, path?: string} $options
     */
    public function setConfig(array $options): self
    {
        $this->source = (string) ($options['source'] ?? $options['path'] ?? '');

        return $this;
    }

    /**
     * @return array{comprobanteSequence: list<array{name: string, type: ?string, minOccurs: ?string, maxOccurs: ?string}>, jsonLike: array<string, mixed>}
     */
    public function process(): array
    {
        if ($this->source === '') {
            throw new \RuntimeException('XSD source not configured');
        }

        $doc = XsdLoader::getInstance()->loadXsd($this->source);
        $flat = XmlUtils::comprobanteSequence($doc);

        return [
            'comprobanteSequence' => $flat,
            'jsonLike' => self::toJsonLikeOutline($flat),
        ];
    }

    /**
     * @param list<array{name: string, type: ?string, minOccurs: ?string, maxOccurs: ?string}> $elements
     */
    private static function toJsonLikeOutline(array $elements): array
    {
        $props = [];
        foreach ($elements as $el) {
            $props[$el['name']] = array_filter(
                [
                    'xsdType' => $el['type'],
                    'minOccurs' => $el['minOccurs'],
                    'maxOccurs' => $el['maxOccurs'],
                ],
                static fn ($v) => $v !== null
            );
        }

        return [
            'type' => 'object',
            'title' => 'Comprobante',
            'properties' => $props,
        ];
    }
}
