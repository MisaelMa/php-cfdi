<?php

declare(strict_types=1);

use Sat\Banxico\BanxicoClient;
use Sat\Banxico\Moneda;
use Sat\Banxico\TipoCambio;

test('BanxicoClient rechaza token vacío', function () {
    new BanxicoClient('');
})->throws(InvalidArgumentException::class);

test('BanxicoClient rechaza token solo espacios', function () {
    new BanxicoClient('   ');
})->throws(InvalidArgumentException::class);

test('obtenerTipoCambio rechaza rango de fechas inválido', function () {
    $c = new BanxicoClient('dummy-token');
    $fin = new DateTimeImmutable('2024-01-01');
    $inicio = new DateTimeImmutable('2024-01-02');
    $c->obtenerTipoCambio(Moneda::USD, $inicio, $fin);
})->throws(InvalidArgumentException::class);

test('Moneda expone los códigos ISO esperados', function () {
    expect(Moneda::USD->value)->toBe('USD');
    expect(Moneda::EUR->value)->toBe('EUR');
    expect(Moneda::GBP->value)->toBe('GBP');
    expect(Moneda::JPY->value)->toBe('JPY');
    expect(Moneda::CAD->value)->toBe('CAD');
    expect(Moneda::cases())->toHaveCount(5);
});

test('TipoCambio expone fecha, valor y moneda', function () {
    $tc = new TipoCambio(fecha: '2024-01-15', valor: 17.25, moneda: Moneda::USD);
    expect($tc->fecha)->toBe('2024-01-15');
    expect($tc->valor)->toBe(17.25);
    expect($tc->moneda)->toBe(Moneda::USD);
});

test('parseSerieObservacionesRango omite N/E y respeta comas en el número', function () {
    $json = [
        'bmx' => [
            'series' => [
                [
                    'datos' => [
                        ['fecha' => '2024-01-02', 'dato' => '17,123.45'],
                        ['fecha' => '2024-01-03', 'dato' => 'N/E'],
                    ],
                ],
            ],
        ],
    ];
    $ref = new ReflectionClass(BanxicoClient::class);
    $m = $ref->getMethod('parseSerieObservacionesRango');
    $m->setAccessible(true);
    /** @var list<TipoCambio> $out */
    $out = $m->invoke(null, Moneda::USD, $json);
    expect($out)->toHaveCount(1);
    expect($out[0]->fecha)->toBe('2024-01-02');
    expect($out[0]->valor)->toBe(17123.45);
    expect($out[0]->moneda)->toBe(Moneda::USD);
});

test('parseUltimoTipoCambio usa el último dato de la serie', function () {
    $json = [
        'bmx' => [
            'series' => [
                [
                    'datos' => [
                        ['fecha' => '2024-01-01', 'dato' => '16.00'],
                        ['fecha' => '2024-01-02', 'dato' => '17.00'],
                    ],
                ],
            ],
        ],
    ];
    $ref = new ReflectionClass(BanxicoClient::class);
    $m = $ref->getMethod('parseUltimoTipoCambio');
    $m->setAccessible(true);
    /** @var TipoCambio $out */
    $out = $m->invoke(null, Moneda::EUR, $json);
    expect($out->fecha)->toBe('2024-01-02');
    expect($out->valor)->toBe(17.0);
    expect($out->moneda)->toBe(Moneda::EUR);
});
