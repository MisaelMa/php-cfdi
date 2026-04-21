<?php

declare(strict_types=1);

use Sat\Scraper\CfdiConsultaResult;
use Sat\Scraper\ConsultaCfdiParams;
use Sat\Scraper\CredencialCiec;
use Sat\Scraper\SatPortal;
use Sat\Scraper\ScraperConfig;
use Sat\Scraper\SesionSat;
use Sat\Scraper\TipoAutenticacion;

test('TipoAutenticacion usa los valores del portal', function () {
    expect(TipoAutenticacion::Ciec->value)->toBe('ciec');
    expect(TipoAutenticacion::Fiel->value)->toBe('fiel');
});

test('CredencialCiec fija tipo CIEC por defecto', function () {
    $c = new CredencialCiec(rfc: 'AAA010101AAA', password: 'x');
    expect($c->tipo)->toBe(TipoAutenticacion::Ciec);
});

test('ScraperConfig acepta valores por defecto', function () {
    $c = new ScraperConfig();
    expect($c->timeoutMs)->toBeNull();
    expect($c->userAgent)->toBeNull();
    expect($c->baseUrl)->toBeNull();
});

test('consultarCfdis exige sesión autenticada', function () {
    $portal = new SatPortal();
    $sesion = new SesionSat(cookies: [], rfc: 'AAA010101AAA', authenticated: false);
    $params = new ConsultaCfdiParams(fechaInicio: '2024-01-01', fechaFin: '2024-01-31');
    $portal->consultarCfdis($sesion, $params);
})->throws(RuntimeException::class, 'no esta activa');

test('consultarCfdis rechaza sesión expirada', function () {
    $portal = new SatPortal();
    $pasado = (new DateTimeImmutable())->modify('-1 hour');
    $sesion = new SesionSat(
        cookies: ['x' => 'y'],
        rfc: 'AAA010101AAA',
        authenticated: true,
        expiresAt: $pasado,
    );
    $params = new ConsultaCfdiParams(fechaInicio: '2024-01-01', fechaFin: '2024-01-31');
    $portal->consultarCfdis($sesion, $params);
})->throws(RuntimeException::class, 'expirado');

test('parseConsultaResults interpreta filas rgRow', function () {
    $html = <<<'HTML'
<tr class="rgRow">
<td>uuid-1</td><td>AAA010101AAA</td><td>Emisor</td><td>BBB020202BBB</td><td>Receptor</td>
<td>2024-01-10</td><td>2024-01-11</td><td>1,234.50</td><td>Ingreso</td><td>Vigente</td>
</tr>
HTML;

    $ref = new ReflectionClass(SatPortal::class);
    $m = $ref->getMethod('parseConsultaResults');
    $m->setAccessible(true);
    $portal = new SatPortal();
    /** @var list<CfdiConsultaResult> $rows */
    $rows = $m->invoke($portal, $html);

    expect($rows)->toHaveCount(1);
    expect($rows[0]->uuid)->toBe('uuid-1');
    expect($rows[0]->rfcEmisor)->toBe('AAA010101AAA');
    expect($rows[0]->total)->toBe(1234.5);
    expect($rows[0]->efecto)->toBe('Ingreso');
    expect($rows[0]->estado)->toBe('Vigente');
});

test('extractCookiesFromHeaders parsea Set-Cookie', function () {
    $ref = new ReflectionClass(SatPortal::class);
    $m = $ref->getMethod('extractCookiesFromHeaders');
    $m->setAccessible(true);
    $headers = [
        'HTTP/1.1 302 Found',
        'Set-Cookie: sessionid=abc123; Path=/; HttpOnly',
        'Set-Cookie: other=val; Path=/',
    ];
    /** @var array<string, string> $cookies */
    $cookies = $m->invoke(null, $headers);
    expect($cookies['sessionid'])->toBe('abc123');
    expect($cookies['other'])->toBe('val');
});
