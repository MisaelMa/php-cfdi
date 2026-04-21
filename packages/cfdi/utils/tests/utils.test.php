<?php

use Cfdi\Utils\NumeroALetras;
use Cfdi\Utils\File;

describe('NumeroALetras', function () {
    $converter = new NumeroALetras();

    test('convierte cero', function () use ($converter) {
        $result = $converter->convertir(0);
        expect($result)->toBe('CERO PESOS 00/100');
    });

    test('convierte uno', function () use ($converter) {
        $result = $converter->convertir(1);
        expect($result)->toBe('UN PESO 00/100');
    });

    test('convierte diez', function () use ($converter) {
        $result = $converter->convertir(10);
        expect($result)->toContain('DIEZ');
    });

    test('convierte cien', function () use ($converter) {
        $result = $converter->convertir(100);
        expect($result)->toContain('CIEN');
    });

    test('convierte mil', function () use ($converter) {
        $result = $converter->convertir(1000);
        expect($result)->toContain('UN MIL');
    });

    test('convierte un millon', function () use ($converter) {
        $result = $converter->convertir(1000000);
        expect($result)->toContain('UN MILLON DE');
    });

    test('convierte con centavos', function () use ($converter) {
        $result = $converter->convertir(1500.50);
        expect($result)->toContain('UN MIL QUINIENTOS');
        expect($result)->toContain('50/100 M.N');
    });

    test('convierte con un centavo', function () use ($converter) {
        $result = $converter->convertir(100.01);
        expect($result)->toContain('01/100 M.N');
    });

    test('convierte con moneda personalizada', function () use ($converter) {
        $result = $converter->convertir(1, 'DOLARES', 'DOLAR');
        expect($result)->toBe('UN DOLAR 00/100');
    });

    test('convierte con moneda plural personalizada', function () use ($converter) {
        $result = $converter->convertir(5, 'DOLARES', 'DOLAR');
        expect($result)->toContain('DOLARES');
    });

    test('convierte numeros grandes', function () use ($converter) {
        $result = $converter->convertir(999999);
        expect($result)->toContain('NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE');
    });

    test('convierte 15', function () use ($converter) {
        $result = $converter->convertir(15);
        expect($result)->toContain('QUINCE');
    });

    test('convierte 21', function () use ($converter) {
        $result = $converter->convertir(21);
        expect($result)->toContain('VEINTIUN');
    });

    test('convierte 50', function () use ($converter) {
        $result = $converter->convertir(50);
        expect($result)->toContain('CINCUENTA');
    });

    test('convierte 200', function () use ($converter) {
        $result = $converter->convertir(200);
        expect($result)->toContain('DOSCIENTOS');
    });

    test('convierte 2500000', function () use ($converter) {
        $result = $converter->convertir(2500000);
        expect($result)->toContain('DOS MILLONES DE');
        expect($result)->toContain('QUINIENTOS');
    });
});

describe('File', function () {
    test('detecta ruta con slash', function () {
        expect(File::isPath('/path/to/file'))->toBeTrue();
    });

    test('detecta ruta con backslash', function () {
        expect(File::isPath('C:\\path\\to\\file'))->toBeTrue();
    });

    test('detecta ruta con extension', function () {
        expect(File::isPath('archivo.xml'))->toBeTrue();
    });

    test('no detecta texto sin ruta', function () {
        expect(File::isPath('simplename'))->toBeFalse();
    });

    test('no detecta cadena vacia como ruta', function () {
        expect(File::isPath(''))->toBeFalse();
    });
});
