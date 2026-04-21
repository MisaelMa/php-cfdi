<?php

namespace Cfdi\Csf;

use RuntimeException;

final class CsfParser
{
    public function __construct(
        private readonly string $pdfPath,
    ) {
    }

    public function extract(): array
    {
        return self::parse($this->pdfPath);
    }

    public static function parse(string $pdfPath): array
    {
        if (!is_readable($pdfPath)) {
            throw new RuntimeException('No se puede leer el archivo: ' . $pdfPath);
        }

        $cmd = 'pdftotext -layout ' . escapeshellarg($pdfPath) . ' -';
        $text = shell_exec($cmd);
        if ($text === null || $text === '') {
            throw new RuntimeException(
                'No se obtuvo texto del PDF. Verifique que pdftotext este instalado.'
            );
        }

        return self::parseFromText($text);
    }

    public static function parseFromText(string $text): array
    {
        $lines = preg_split('/\R/u', $text) ?: [];
        $trimmed = array_map(trim(...), $lines);
        $data = array_values(array_filter($trimmed, static fn (string $l): bool => $l !== ''));

        return self::buildFromLines($data);
    }

    /**
     * @param list<string> $data
     * @return array<string, string>
     */
    private static function buildFromLines(array $data): array
    {
        $cif = self::lineContaining($data, 'idCIF:');
        $rfcIndex = self::findIndex($data, 'RFC:');
        $curpIndex = self::findIndex($data, 'CURP:');
        $nombreIndex = self::findIndex($data, 'Nombre (s):');
        $paIndex = self::findIndex($data, 'Primer Apellido:');
        $saIndex = self::findIndex($data, 'Segundo Apellido:');
        $fioIndex = self::findIndex($data, 'Fecha inicio de operaciones:');
        $padronIndex = self::findIndex($data, 'padrón:');
        $fucsIndex = self::findIndex($data, 'estado:');
        $ncIndex = self::findIndex($data, 'Comercial:');

        $rfc = $rfcIndex >= 0 && isset($data[$rfcIndex + 1]) ? $data[$rfcIndex + 1] : '';

        return [
            'id_cif' => $cif !== null ? self::afterColonFirstSpace($cif) : '',
            'rfc' => $rfc,
            'curp' => $curpIndex >= 0 && isset($data[$curpIndex + 1]) ? $data[$curpIndex + 1] : '',
            'nombre' => $nombreIndex >= 0 && isset($data[$nombreIndex + 1]) ? $data[$nombreIndex + 1] : '',
            'primer_apellido' => $paIndex >= 0 && isset($data[$paIndex + 1]) ? $data[$paIndex + 1] : '',
            'segundo_apellido' => $saIndex >= 0 && isset($data[$saIndex + 1]) ? $data[$saIndex + 1] : '',
            'fecha_inicio_de_operaciones' => $fioIndex >= 0 && isset($data[$fioIndex + 1]) ? $data[$fioIndex + 1] : '',
            'estatus_en_el_padrón' => $padronIndex >= 0 && isset($data[$padronIndex + 1]) ? $data[$padronIndex + 1] : '',
            'fecha_de_último_cambio_de_estado' => $fucsIndex >= 0 && isset($data[$fucsIndex + 1]) ? $data[$fucsIndex + 1] : '',
            'nombre_comercial' => $ncIndex >= 0 && isset($data[$ncIndex + 1]) ? $data[$ncIndex + 1] : '',
            'cp' => self::findIndexSplit($data, 'Postal:'),
            'tipo_de_vialidad' => self::findIndexSplit($data, 'Tipo de Vialidad:'),
            'nombre_de_vialidad' => self::findIndexSplit($data, 'Nombre de Vialidad:'),
            'numero_exterior' => self::findIndexSplit($data, 'Exterior:'),
            'numero_interior' => self::findIndexSplit($data, 'Interior:'),
            'nombre_de_la_colonia' => self::findIndexSplit($data, 'Colonia:'),
            'nombre_de_la_localidad' => self::findIndexSplit($data, 'Localidad:'),
            'nombre_del_municipio' => self::findIndexSplit($data, 'Territorial:'),
            'nombre_de_la_entidad_federativa' => self::findIndexSplit($data, 'Federativa:'),
            'entre_calle' => self::findIndexSplit($data, 'Entre Calle:'),
            'y_calle' => self::findIndexSplit($data, 'Y Calle:'),
            'regimen' => isset($data[58]) ? $data[58] : '',
            'RegimenFiscal' => isset($data[58]) ? $data[58] : '',
        ];
    }

    /**
     * @param list<string> $data
     */
    private static function findIndex(array $data, string $word): int
    {
        foreach ($data as $i => $item) {
            if (str_contains($item, $word)) {
                return (int) $i;
            }
        }

        return -1;
    }

    /**
     * @param list<string> $data
     */
    private static function lineContaining(array $data, string $word): ?string
    {
        foreach ($data as $item) {
            if (str_contains($item, $word)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param list<string> $data
     */
    private static function findIndexSplit(array $data, string $word): string
    {
        foreach ($data as $item) {
            if (str_contains($item, $word)) {
                $parts = explode(':', $item, 2);

                return isset($parts[1]) ? self::removeFirstSpace($parts[1]) : '';
            }
        }

        return '';
    }

    private static function afterColonFirstSpace(string $text): string
    {
        $parts = explode(':', $text, 2);

        return isset($parts[1]) ? self::removeFirstSpace($parts[1]) : '';
    }

    private static function removeFirstSpace(string $s): string
    {
        if ($s !== '' && $s[0] === ' ') {
            return substr($s, 1);
        }

        return $s;
    }
}
