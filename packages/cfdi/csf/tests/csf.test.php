<?php

use Cfdi\Csf\CsfParser;

$sampleText = <<<'TXT'
idCIF: 12345678
RFC:
XAXX010101000
CURP:
CURP010101HDFXXX01
Nombre (s):
JUAN
Primer Apellido:
PEREZ
Segundo Apellido:
LOPEZ
Fecha inicio de operaciones:
01/01/2020
padrón:
ACTIVO
estado:
VIGENTE
Comercial:
MI NEGOCIO
Código Postal: 01000
Tipo de Vialidad: CALLE
Nombre de Vialidad: REFORMA
Número Exterior: 100
Número Interior: A
Nombre de la Colonia: CENTRO
Nombre de la Localidad: CDMX
Nombre del Municipio o Demarcación Territorial: CUAUHTEMOC
Nombre de la Entidad Federativa: CIUDAD DE MEXICO
Entre Calle: A
Y Calle: B
Regimen Fiscal
601
TXT;

describe('CsfParser', function () use ($sampleText) {
    test('parseFromText extrae RFC y CURP', function () use ($sampleText) {
        $data = CsfParser::parseFromText($sampleText);
        expect($data['rfc'])->toBe('XAXX010101000');
        expect($data['curp'])->toBe('CURP010101HDFXXX01');
    });

    test('parseFromText extrae nombre y apellidos', function () use ($sampleText) {
        $data = CsfParser::parseFromText($sampleText);
        expect($data['nombre'])->toBe('JUAN');
        expect($data['primer_apellido'])->toBe('PEREZ');
        expect($data['segundo_apellido'])->toBe('LOPEZ');
    });

    test('parseFromText extrae id_cif', function () use ($sampleText) {
        $data = CsfParser::parseFromText($sampleText);
        expect($data['id_cif'])->toBe('12345678');
    });

    test('parseFromText extrae campos de domicilio por etiqueta', function () use ($sampleText) {
        $data = CsfParser::parseFromText($sampleText);
        expect($data['cp'])->toBe('01000');
        expect($data['tipo_de_vialidad'])->toBe('CALLE');
        expect($data['nombre_de_vialidad'])->toBe('REFORMA');
    });

    test('instanciacion con ruta y extract delega a parse', function () {
        $parser = new CsfParser('/ruta/inexistente.pdf');
        expect(fn () => $parser->extract())->toThrow(\RuntimeException::class);
    });
});
