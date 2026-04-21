<?php

declare(strict_types=1);

use Sat\Opinion\ObligacionFiscal;
use Sat\Opinion\OpinionConfig;
use Sat\Opinion\OpinionCumplimiento;
use Sat\Opinion\OpinionCumplimientoDatos;
use Sat\Opinion\ResultadoOpinion;
use Sat\Opinion\SesionPortal;

test('ResultadoOpinion usa los valores literales del SAT', function () {
    expect(ResultadoOpinion::Positivo->value)->toBe('Positivo');
    expect(ResultadoOpinion::Negativo->value)->toBe('Negativo');
    expect(ResultadoOpinion::EnSuspenso->value)->toBe('En suspenso');
    expect(ResultadoOpinion::InscritoSinObligaciones->value)->toBe('Inscrito sin obligaciones');
    expect(ResultadoOpinion::NoInscrito->value)->toBe('No inscrito');
});

test('OpinionConfig acepta valores por defecto', function () {
    $c = new OpinionConfig();
    expect($c->timeoutMs)->toBeNull();
    expect($c->baseUrl)->toBeNull();
});

test('obtener exige sesión autenticada', function () {
    $svc = new OpinionCumplimiento();
    $sesion = new SesionPortal(cookies: [], rfc: 'AAA010101AAA', authenticated: false);
    $svc->obtener($sesion);
})->throws(RuntimeException::class, 'sesion activa');

test('descargarPdf exige sesión autenticada', function () {
    $svc = new OpinionCumplimiento();
    $sesion = new SesionPortal(cookies: [], rfc: 'AAA010101AAA', authenticated: false);
    $svc->descargarPdf($sesion);
})->throws(RuntimeException::class, 'sesion activa');

test('extractResultado detecta cada variante en HTML', function () {
    $ref = new ReflectionClass(OpinionCumplimiento::class);
    $m = $ref->getMethod('extractResultado');
    $m->setAccessible(true);
    $svc = new OpinionCumplimiento();

    expect($m->invoke($svc, '<div>RESULTADO Positivo</div>'))->toBe(ResultadoOpinion::Positivo);
    expect($m->invoke($svc, 'NEGATIVO'))->toBe(ResultadoOpinion::Negativo);
    expect($m->invoke($svc, 'en suspenso'))->toBe(ResultadoOpinion::EnSuspenso);
    expect($m->invoke($svc, 'inscrito sin obligaciones fiscales'))->toBe(ResultadoOpinion::InscritoSinObligaciones);
    expect($m->invoke($svc, 'otro texto'))->toBe(ResultadoOpinion::NoInscrito);
});

test('parseOpinion extrae campos y obligaciones', function () {
    $html = <<<'HTML'
<table>
<tr><td colspan="4">Obligaciones</td></tr>
<tr><td>ISR</td><td>2020-01-01</td><td></td><td>Activa</td></tr>
</table>
Nombre, denominación o razón social:<span> ACME SA </span>
Folio:<span> F-123 </span>
Fecha de emisión:<span> 2024-06-01 </span>
<div>Positivo</div>
HTML;

    $ref = new ReflectionClass(OpinionCumplimiento::class);
    $m = $ref->getMethod('parseOpinion');
    $m->setAccessible(true);
    $svc = new OpinionCumplimiento();
    /** @var OpinionCumplimientoDatos $out */
    $out = $m->invoke($svc, $html, 'AAA010101AAA');

    expect($out->rfc)->toBe('AAA010101AAA');
    expect($out->nombreContribuyente)->toBe('ACME SA');
    expect($out->folioOpinion)->toBe('F-123');
    expect($out->fechaEmision)->toBe('2024-06-01');
    expect($out->resultado)->toBe(ResultadoOpinion::Positivo);
    expect($out->obligaciones)->toHaveCount(1);
    expect($out->obligaciones[0])->toBeInstanceOf(ObligacionFiscal::class);
    expect($out->obligaciones[0]->descripcion)->toBe('ISR');
    expect($out->obligaciones[0]->fechaInicio)->toBe('2020-01-01');
});
