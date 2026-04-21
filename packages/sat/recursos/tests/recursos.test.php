<?php

declare(strict_types=1);

use Sat\Recursos\SatResources;

test('SatResources 4.0 acepta version y directorio de salida', function () {
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sat_recursos_test_' . bin2hex(random_bytes(4));
    $r = new SatResources('4.0', $dir);
    expect($r)->toBeInstanceOf(SatResources::class);
});

test('SatResources 3.3 acepta version y directorio de salida', function () {
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sat_recursos_test_' . bin2hex(random_bytes(4));
    $r = new SatResources('3.3', $dir);
    expect($r)->toBeInstanceOf(SatResources::class);
});

test('SatResources rechaza version invalida', function () {
    new SatResources('2.0', '/tmp');
})->throws(InvalidArgumentException::class);

test('cleanXml elimina texto antes de la declaracion XML', function () {
    $dir = sys_get_temp_dir();
    $r = new SatResources('4.0', $dir);
    $raw = "This XML file does not appear to have any style information.\n<?xml version=\"1.0\"?><root/>";
    $clean = $r->cleanXml($raw);
    expect(str_starts_with($clean, '<?xml'))->toBeTrue();
    expect($clean)->toContain('<root/>');
});

test('cleanXml encuentra xsl:stylesheet sin declaracion previa y agrega XML declaration', function () {
    $r = new SatResources('4.0', sys_get_temp_dir());
    $raw = "noise\n<xsl:stylesheet version=\"1.0\" xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\"/>";
    $clean = $r->cleanXml($raw);
    expect(str_starts_with($clean, '<?xml version="1.0" encoding="UTF-8"?>'))->toBeTrue();
    expect($clean)->toContain('<xsl:stylesheet');
});

test('extractSchemaImports detecta catCFDI y tipoDatos', function () {
    $r = new SatResources('4.0', sys_get_temp_dir());
    $ref = new ReflectionClass(SatResources::class);
    $m = $ref->getMethod('extractSchemaImports');
    $m->setAccessible(true);
    $schema = <<<'XSD'
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:import namespace="http://www.sat.gob.mx/sitio_internet/cfd/catalogos" schemaLocation="http://example.com/catCFDI.xsd"/>
  <xs:import namespace="http://www.sat.gob.mx/sitio_internet/cfd/td" schemaLocation="https://example.com/tdCFDI.xsd"/>
</xs:schema>
XSD;
    /** @var array{catalogUrl: string|null, tipoDatosUrl: string|null} $out */
    $out = $m->invoke($r, $schema);
    expect($out['catalogUrl'])->toBe('http://example.com/catCFDI.xsd');
    expect($out['tipoDatosUrl'])->toBe('https://example.com/tdCFDI.xsd');
});

test('extractXslIncludes solo incluye hrefs http(s)', function () {
    $r = new SatResources('4.0', sys_get_temp_dir());
    $ref = new ReflectionClass(SatResources::class);
    $m = $ref->getMethod('extractXslIncludes');
    $m->setAccessible(true);
    $xslt = <<<'XSLT'
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:include href="https://sat.example.com/foo.xslt"/>
  <xsl:include href="./local.xslt"/>
</xsl:stylesheet>
XSLT;
    /** @var list<string> $urls */
    $urls = $m->invoke($r, $xslt);
    expect($urls)->toBe(['https://sat.example.com/foo.xslt']);
});

test('rewriteIncludes reemplaza URLs absolutas por rutas locales', function () {
    $r = new SatResources('4.0', sys_get_temp_dir());
    $ref = new ReflectionClass(SatResources::class);
    $m = $ref->getMethod('rewriteIncludes');
    $m->setAccessible(true);
    $xslt = '<xsl:include href="https://www.sat.gob.mx/path/donat11.xslt"/>';
    $urls = ['https://www.sat.gob.mx/path/donat11.xslt'];
    $out = $m->invoke($r, $xslt, $urls);
    expect($out)->toContain('href="./complementos/donat11.xslt"');
});

test('diffComplementos calcula unused y added', function () {
    $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sat_recursos_diff_' . bin2hex(random_bytes(4));
    $comp = $base . DIRECTORY_SEPARATOR . 'complementos';
    mkdir($comp, 0775, true);
    file_put_contents($comp . DIRECTORY_SEPARATOR . 'old.xslt', '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');

    $r = new SatResources('4.0', $base);
    $ref = new ReflectionClass(SatResources::class);
    $m = $ref->getMethod('diffComplementos');
    $m->setAccessible(true);
    /** @var array{unused: list<string>, added: list<string>} $diff */
    $diff = $m->invoke($r, $comp, ['old.xslt' => true, 'new.xslt' => true]);
    expect($diff['unused'])->toBe([]);
    expect($diff['added'])->toBe(['new.xslt']);
});
