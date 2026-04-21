<?php

use Cli\Openssl\Pkcs8;

test('pkcs8 builds basic command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->cli();

    expect($cli)->toContain('openssl');
    expect($cli)->toContain('pkcs8');
});

test('pkcs8 builds inform command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->inform('DER')->cli();

    expect($cli)->toContain('-inform DER');
});

test('pkcs8 builds outform command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->outform('PEM')->cli();

    expect($cli)->toContain('-outform PEM');
});

test('pkcs8 builds in command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->in('/path/to/key.key')->cli();

    expect($cli)->toContain('-in /path/to/key.key');
});

test('pkcs8 builds topk8 command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->topk8()->cli();

    expect($cli)->toContain('-topk8');
});

test('pkcs8 builds traditional command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->traditional()->cli();

    expect($cli)->toContain('-traditional');
});

test('pkcs8 builds nocrypt command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->nocrypt()->cli();

    expect($cli)->toContain('-nocrypt');
});

test('pkcs8 builds iter command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->iter(2048)->cli();

    expect($cli)->toContain('-iter 2048');
});

test('pkcs8 builds rand command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->rand('/dev/urandom')->cli();

    expect($cli)->toContain('-rand /dev/urandom');
});

test('pkcs8 builds passin command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->passin('pass:12345678a')->cli();

    expect($cli)->toContain('-passin pass:12345678a');
});

test('pkcs8 builds passout command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->passout('pass:newpass')->cli();

    expect($cli)->toContain('-passout pass:newpass');
});

test('pkcs8 chains DER key conversion to PEM', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->inform('DER')
        ->in('/path/to/key.key')
        ->outform('PEM')
        ->passin('pass:12345678a')
        ->cli();

    expect($cli)->toContain('openssl pkcs8');
    expect($cli)->toContain('-inform DER');
    expect($cli)->toContain('-in /path/to/key.key');
    expect($cli)->toContain('-outform PEM');
    expect($cli)->toContain('-passin pass:12345678a');
});

test('pkcs8 chains nocrypt + topk8', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->topk8()
        ->nocrypt()
        ->in('/path/to/key.pem')
        ->cli();

    expect($cli)->toContain('-topk8');
    expect($cli)->toContain('-nocrypt');
    expect($cli)->toContain('-in /path/to/key.pem');
});

test('pkcs8 builds out command', function () {
    $pkcs8 = new Pkcs8();
    $cli = $pkcs8->out('/path/to/output.pem')->cli();

    expect($cli)->toContain('-out /path/to/output.pem');
});

test('pkcs8 resets commandline after cli() call', function () {
    $pkcs8 = new Pkcs8();
    $cli1 = $pkcs8->inform('DER')->in('/path/to/key.key')->passin('pass:12345678a')->cli();
    $cli2 = $pkcs8->nocrypt()->topk8()->cli();

    expect($cli1)->toContain('-inform DER');
    expect($cli1)->toContain('-passin pass:12345678a');
    expect($cli2)->not->toContain('-inform DER');
    expect($cli2)->not->toContain('-passin');
    expect($cli2)->toContain('-nocrypt');
    expect($cli2)->toContain('-topk8');
});
