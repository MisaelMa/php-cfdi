<?php

use Cli\Openssl\X509;

test('x509 builds inform command', function () {
    $x509 = new X509();
    $cli = $x509->inform('DER')->cli();

    expect($cli)->toContain('openssl');
    expect($cli)->toContain('x509');
    expect($cli)->toContain('-inform DER');
});

test('x509 builds outform command', function () {
    $x509 = new X509();
    $cli = $x509->outform('PEM')->cli();

    expect($cli)->toContain('-outform PEM');
});

test('x509 builds in command', function () {
    $x509 = new X509();
    $cli = $x509->in('/path/to/cert.cer')->cli();

    expect($cli)->toContain('-in /path/to/cert.cer');
});

test('x509 builds noout command', function () {
    $x509 = new X509();
    $cli = $x509->noout()->cli();

    expect($cli)->toContain('-noout');
});

test('x509 chains inform + in + noout + startdate', function () {
    $x509 = new X509();
    $cli = $x509->inform('DER')
        ->in('/path/to/cert.cer')
        ->noout()
        ->startdate()
        ->cli();

    expect($cli)->toContain('openssl x509');
    expect($cli)->toContain('-inform DER');
    expect($cli)->toContain('-in /path/to/cert.cer');
    expect($cli)->toContain('-noout');
    expect($cli)->toContain('-startdate');
});

test('x509 chains inform + in + noout + enddate', function () {
    $x509 = new X509();
    $cli = $x509->inform('DER')
        ->in('/path/to/cert.cer')
        ->noout()
        ->enddate()
        ->cli();

    expect($cli)->toContain('-enddate');
});

test('x509 chains inform + in + noout + subject', function () {
    $x509 = new X509();
    $cli = $x509->inform('DER')
        ->in('/path/to/cert.cer')
        ->noout()
        ->subject()
        ->cli();

    expect($cli)->toContain('-subject');
});

test('x509 chains inform + in + noout + issuer', function () {
    $x509 = new X509();
    $cli = $x509->inform('DER')
        ->in('/path/to/cert.cer')
        ->noout()
        ->issuer()
        ->cli();

    expect($cli)->toContain('-issuer');
});

test('x509 chains inform + in + outform for PEM conversion', function () {
    $x509 = new X509();
    $cli = $x509->inform('DER')
        ->in('/path/to/cert.cer')
        ->outform('PEM')
        ->cli();

    expect($cli)->toContain('-inform DER');
    expect($cli)->toContain('-outform PEM');
});

test('x509 builds serial command', function () {
    $x509 = new X509();
    $cli = $x509->inform('DER')
        ->in('/path/to/cert.cer')
        ->noout()
        ->serial()
        ->cli();

    expect($cli)->toContain('-serial');
});

test('x509 builds pubkey command', function () {
    $x509 = new X509();
    $cli = $x509->inform('DER')
        ->in('/path/to/cert.cer')
        ->noout()
        ->pubkey()
        ->cli();

    expect($cli)->toContain('-pubkey');
});

test('x509 builds modulus command', function () {
    $x509 = new X509();
    $cli = $x509->noout()->modulus()->cli();

    expect($cli)->toContain('-modulus');
});

test('x509 builds text command', function () {
    $x509 = new X509();
    $cli = $x509->text()->cli();

    expect($cli)->toContain('-text');
});

test('x509 builds fingerprint command', function () {
    $x509 = new X509();
    $cli = $x509->fingerprint()->cli();

    expect($cli)->toContain('-fingerprint');
});

test('x509 builds dates command', function () {
    $x509 = new X509();
    $cli = $x509->dates()->cli();

    expect($cli)->toContain('-dates');
});

test('x509 builds checkend command', function () {
    $x509 = new X509();
    $cli = $x509->checkend(3600)->cli();

    expect($cli)->toContain('-checkend 3600');
});

test('x509 builds keyform command', function () {
    $x509 = new X509();
    $cli = $x509->keyform('PEM')->cli();

    expect($cli)->toContain('-keyform PEM');
});

test('x509 keyform rejects invalid option', function () {
    $x509 = new X509();
    $x509->keyform('INVALID');
})->throws(\InvalidArgumentException::class);

test('x509 builds hash command', function () {
    $x509 = new X509();
    $cli = $x509->hash()->cli();

    expect($cli)->toContain('-hash');
});

test('x509 builds subject_hash command', function () {
    $x509 = new X509();
    $cli = $x509->subject_hash()->cli();

    expect($cli)->toContain('-subject_hash');
});

test('x509 builds issuer_hash command', function () {
    $x509 = new X509();
    $cli = $x509->issuer_hash()->cli();

    expect($cli)->toContain('-issuer_hash');
});

test('x509 builds CA commands', function () {
    $x509 = new X509();
    $cli = $x509->CA('/path/ca.pem')
        ->CAkey('/path/ca.key')
        ->CAserial('/path/ca.srl')
        ->cli();

    expect($cli)->toContain('-CA /path/ca.pem');
    expect($cli)->toContain('-CAkey /path/ca.key');
    expect($cli)->toContain('-CAserial /path/ca.srl');
});

test('x509 builds CAcreateserial command', function () {
    $x509 = new X509();
    $cli = $x509->CAcreateserial()->cli();

    expect($cli)->toContain('-CAcreateserial');
});

test('x509 builds days command', function () {
    $x509 = new X509();
    $cli = $x509->days('365')->cli();

    expect($cli)->toContain('-days 365');
});

test('x509 builds req command', function () {
    $x509 = new X509();
    $cli = $x509->req()->cli();

    expect($cli)->toContain('-req');
});

test('x509 builds set_serial command', function () {
    $x509 = new X509();
    $cli = $x509->set_serial('01')->cli();

    expect($cli)->toContain('-set_serial 01');
});

test('x509 resets commandline after cli() call', function () {
    $x509 = new X509();
    $cli1 = $x509->inform('DER')->in('/path/cert.cer')->noout()->startdate()->cli();
    $cli2 = $x509->noout()->enddate()->cli();

    expect($cli1)->toContain('-startdate');
    expect($cli2)->not->toContain('-startdate');
    expect($cli2)->toContain('-enddate');
});

test('x509 builds nameopt command', function () {
    $x509 = new X509();
    $cli = $x509->nameopt('RFC2253')->cli();

    expect($cli)->toContain('-nameopt RFC2253');
});

test('x509 builds email command', function () {
    $x509 = new X509();
    $cli = $x509->email()->cli();

    expect($cli)->toContain('-email');
});

test('x509 builds engine command', function () {
    $x509 = new X509();
    $cli = $x509->engine('pkcs11')->cli();

    expect($cli)->toContain('-engine pkcs11');
});

test('x509 builds preserve_dates command', function () {
    $x509 = new X509();
    $cli = $x509->preserve_dates()->cli();

    expect($cli)->toContain('-preserve_dates');
});

test('x509 builds extensions command', function () {
    $x509 = new X509();
    $cli = $x509->extensions('v3_req')->cli();

    expect($cli)->toContain('-extensions v3_req');
});

test('x509 builds force_pubkey command', function () {
    $x509 = new X509();
    $cli = $x509->force_pubkey('/path/pub.pem')->cli();

    expect($cli)->toContain('-force_pubkey /path/pub.pem');
});

test('x509 builds trustout command', function () {
    $x509 = new X509();
    $cli = $x509->trustout()->cli();

    expect($cli)->toContain('-trustout');
});

test('x509 builds setalias command', function () {
    $x509 = new X509();
    $cli = $x509->setalias('myalias')->cli();

    expect($cli)->toContain('-setalias myalias');
});

test('x509 builds purpose command', function () {
    $x509 = new X509();
    $cli = $x509->purpose()->cli();

    expect($cli)->toContain('-purpose');
});
