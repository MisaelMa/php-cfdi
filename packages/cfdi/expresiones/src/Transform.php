<?php

namespace Cfdi\Expresiones;

class Transform
{
    /** @var array<string, mixed> */
    private array $xml;

    /**
     * @param array<string, mixed> $xml
     */
    public function __construct(array $xml)
    {
        $this->xml = $xml;
    }

    public function run(): string
    {
        $comprobante = $this->xml['cfdi:Comprobante'] ?? null;
        $rear = $this->obtenerValores(is_array($comprobante) ? $comprobante : null, false);
        $filtered = array_values(array_filter($rear, static function ($e) {
            if ($e === null || $e === false || $e === '' || $e === 0 || $e === 0.0) {
                return false;
            }

            return true;
        }));

        return '||' . implode('|', array_map(static fn ($v) => (string) $v, $filtered)) . '||';
    }

    /**
     * @param array<string, mixed>|list<mixed>|null $obj
     * @return list<mixed>
     */
    private function obtenerValores(?array $obj, bool $ignore = false): array
    {
        if ($obj === null) {
            return [];
        }

        $valores = [];
        $ordenComprobante = [
            '_attributes',
            'cfdi:InformacionGlobal',
            'cfdi:CfdiRelacionados',
            'cfdi:Emisor',
            'cfdi:Receptor',
            'cfdi:Conceptos',
            'cfdi:Impuestos',
            'cfdi:Complemento',
        ];

        $claves = $ignore ? array_keys($obj) : $ordenComprobante;

        foreach ($claves as $key) {
            if (!is_string($key) && !is_int($key)) {
                continue;
            }
            $keyStr = (string) $key;

            if ($this->shouldOmitKey($keyStr)) {
                continue;
            }

            if (!array_key_exists($key, $obj)) {
                continue;
            }

            $value = $obj[$key];

            if (is_array($value)) {
                $valores = array_merge($valores, $this->obtenerValores($value, true));
            } else {
                $valores[] = $value;
            }
        }

        return $valores;
    }

    private function shouldOmitKey(string $key): bool
    {
        if (str_starts_with($key, 'xmlns:')) {
            return true;
        }

        return in_array($key, ['xsi:schemaLocation', 'Certificado', 'Sello'], true);
    }
}
